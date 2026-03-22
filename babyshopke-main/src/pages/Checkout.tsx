import { FormEvent, useEffect, useRef, useState } from "react";
import { useNavigate } from "react-router-dom";
import { toast } from "sonner";
import {
  CheckCircle2,
  Loader2,
  MapPin,
  Phone,
  Smartphone,
  Truck,
  User,
  XCircle,
} from "lucide-react";
import UserPageLayout from "@/components/UserPageLayout";
import { useCommerce } from "@/context/CommerceContext";

// ── Config ────────────────────────────────────────────────────────────────────
const BACKEND_URL = "http://localhost/babyshopke/babyshopke-main/backend/controllers/mpesa_api.php";
const DELIVERY_FEE = 250;
const POLL_INTERVAL_MS = 4000;  // Poll every 4 seconds
const POLL_TIMEOUT_MS  = 90000; // Give up after 90 seconds

type DeliveryOption = "delivery" | "pickup";
type PaymentStatus  = "idle" | "sending" | "awaiting" | "success" | "failed";

const Checkout = () => {
  const navigate = useNavigate();
  const { cartItems, cartTotal, clearCart } = useCommerce();

  // ── Form state ───────────────────────────────────────────────────────────
  const [fullName,      setFullName]      = useState("");
  const [phone,         setPhone]         = useState("");
  const [address,       setAddress]       = useState("");
  const [deliveryOpt,   setDeliveryOpt]   = useState<DeliveryOption>("delivery");
  const [mpesaPhone,    setMpesaPhone]    = useState("");

  // ── Payment flow state ───────────────────────────────────────────────────
  const [payStatus,      setPayStatus]      = useState<PaymentStatus>("idle");
  const [checkoutReqId,  setCheckoutReqId]  = useState<string | null>(null);
  const [orderId,        setOrderId]        = useState<number | null>(null);
  const [statusMessage,  setStatusMessage]  = useState("");
  const [mpesaReceipt,   setMpesaReceipt]   = useState<string | null>(null);

  const pollRef    = useRef<ReturnType<typeof setInterval> | null>(null);
  const timeoutRef = useRef<ReturnType<typeof setTimeout>  | null>(null);

  const orderTotal = deliveryOpt === "delivery" ? cartTotal + DELIVERY_FEE : cartTotal;

  // ── Helpers ───────────────────────────────────────────────────────────────
  const formatPhone = (v: string) => v.replace(/\D/g, "").slice(0, 12);

  const stopPolling = () => {
    if (pollRef.current)    clearInterval(pollRef.current);
    if (timeoutRef.current) clearTimeout(timeoutRef.current);
    pollRef.current    = null;
    timeoutRef.current = null;
  };

  useEffect(() => () => stopPolling(), []);

  // ── Poll for payment status ───────────────────────────────────────────────
  const startPolling = (crid: string) => {
    pollRef.current = setInterval(async () => {
      try {
        const res  = await fetch(BACKEND_URL, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ action: "status", checkout_request_id: crid }),
        });
        const data = await res.json();

        if (data.status === "success") {
          stopPolling();
          setMpesaReceipt(data.mpesa_receipt ?? null);
          setPayStatus("success");
          clearCart();
        } else if (data.status === "failed") {
          stopPolling();
          setStatusMessage(data.message || "Payment was cancelled or failed.");
          setPayStatus("failed");
        }
        // 'pending' → keep polling
      } catch {
        // Network blip — keep polling
      }
    }, POLL_INTERVAL_MS);

    // Hard timeout
    timeoutRef.current = setTimeout(() => {
      stopPolling();
      setStatusMessage("Payment timed out. Please check your M-Pesa messages and try again.");
      setPayStatus("failed");
    }, POLL_TIMEOUT_MS);
  };

  // ── Submit handler ────────────────────────────────────────────────────────
  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    if (cartItems.length === 0) {
      toast.error("Your cart is empty.");
      return;
    }

    // Validate delivery fields
    if (!fullName.trim() || !phone.trim()) {
      toast.error("Full name and phone are required.");
      return;
    }
    if (deliveryOpt === "delivery" && !address.trim()) {
      toast.error("Delivery address is required.");
      return;
    }

    // Validate M-Pesa number
    const mpesaRegex = /^(07|01|\+2547|\+2541)\d{8}$/;
    if (!mpesaRegex.test(mpesaPhone)) {
      toast.error("Enter a valid Safaricom number (e.g. 0712345678).");
      return;
    }

    setPayStatus("sending");
    setStatusMessage("");

    try {
      const res = await fetch(BACKEND_URL, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          action:          "initiate",
          full_name:       fullName.trim(),
          phone:           phone.trim(),
          address:         address.trim(),
          delivery_option: deliveryOpt,
          mpesa_phone:     mpesaPhone.trim(),
          cart_items:      cartItems.map((i) => ({ id: i.id, name: i.name, price: i.price, qty: i.qty })),
        }),
      });

      const data = await res.json();

      if (!data.success) {
        toast.error(data.message || "Failed to send STK Push.");
        setPayStatus("idle");
        return;
      }

      setOrderId(data.order_id);
      setCheckoutReqId(data.checkout_request_id);
      setStatusMessage(data.message);
      setPayStatus("awaiting");
      startPolling(data.checkout_request_id);
      toast.success("STK Push sent! Check your phone.");
    } catch {
      toast.error("Network error. Check your connection and try again.");
      setPayStatus("idle");
    }
  };

  // ── Success screen ────────────────────────────────────────────────────────
  if (payStatus === "success") {
    return (
      <UserPageLayout title="Payment Confirmed" description="Thank you for your order.">
        <div className="max-w-md mx-auto bg-card rounded-2xl border border-border p-8 shadow-soft text-center">
          <CheckCircle2 className="w-16 h-16 text-emerald-500 mx-auto mb-4" />
          <h2 className="text-2xl font-extrabold mb-1">Payment Received!</h2>
          <p className="text-muted-foreground text-sm mb-5">
            Your M-Pesa payment of{" "}
            <span className="font-bold text-foreground">KSH {orderTotal.toLocaleString()}</span>{" "}
            has been confirmed.
          </p>

          <div className="bg-secondary rounded-xl p-4 text-left space-y-2 mb-6 text-sm">
            {orderId && (
              <div className="flex justify-between">
                <span className="text-muted-foreground">Order #</span>
                <span className="font-bold">{orderId}</span>
              </div>
            )}
            {mpesaReceipt && (
              <div className="flex justify-between">
                <span className="text-muted-foreground">M-Pesa Receipt</span>
                <span className="font-bold text-emerald-700">{mpesaReceipt}</span>
              </div>
            )}
            <div className="flex justify-between">
              <span className="text-muted-foreground">Name</span>
              <span className="font-semibold">{fullName}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-muted-foreground">Phone</span>
              <span className="font-semibold">{mpesaPhone}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-muted-foreground">Delivery</span>
              <span className="font-semibold capitalize">{deliveryOpt}</span>
            </div>
            <div className="flex justify-between font-bold border-t border-border pt-2">
              <span>Total Paid</span>
              <span className="text-primary">KSH {orderTotal.toLocaleString()}</span>
            </div>
          </div>

          <div className="flex gap-3">
            <button type="button" onClick={() => navigate("/orders")}
              className="flex-1 py-3 rounded-full bg-primary text-primary-foreground font-bold">
              View Orders
            </button>
            <button type="button" onClick={() => navigate("/shop")}
              className="flex-1 py-3 rounded-full border border-border bg-card font-semibold">
              Shop More
            </button>
          </div>
        </div>
      </UserPageLayout>
    );
  }

  // ── Failed screen ─────────────────────────────────────────────────────────
  if (payStatus === "failed") {
    return (
      <UserPageLayout title="Payment Failed" description="Something went wrong.">
        <div className="max-w-md mx-auto bg-card rounded-2xl border border-border p-8 shadow-soft text-center">
          <XCircle className="w-16 h-16 text-red-500 mx-auto mb-4" />
          <h2 className="text-2xl font-extrabold mb-2">Payment Failed</h2>
          <p className="text-muted-foreground text-sm mb-6">
            {statusMessage || "Your M-Pesa payment was not completed."}
          </p>
          <button type="button" onClick={() => setPayStatus("idle")}
            className="w-full py-3 rounded-full bg-accent text-accent-foreground font-bold">
            Try Again
          </button>
        </div>
      </UserPageLayout>
    );
  }

  // ── Awaiting payment screen ───────────────────────────────────────────────
  if (payStatus === "awaiting") {
    return (
      <UserPageLayout title="Awaiting Payment" description="Complete the payment on your phone.">
        <div className="max-w-md mx-auto bg-card rounded-2xl border border-border p-10 shadow-soft text-center">
          <div className="relative flex justify-center mb-6">
            <div className="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center">
              <Smartphone className="w-10 h-10 text-green-600" />
            </div>
            <Loader2 className="absolute -bottom-1 -right-1 w-7 h-7 animate-spin text-green-600 bg-card rounded-full" />
          </div>

          <h2 className="text-xl font-extrabold mb-2">STK Push Sent!</h2>
          <p className="text-muted-foreground text-sm mb-1">
            A payment prompt was sent to
          </p>
          <p className="font-bold text-foreground text-lg mb-5">{mpesaPhone}</p>

          <div className="bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800 text-left mb-6">
            <ol className="list-decimal list-inside space-y-1.5">
              <li>Your phone will vibrate with a prompt</li>
              <li>Enter your <strong>M-Pesa PIN</strong> to confirm</li>
              <li>You'll see "<strong>KSH {orderTotal.toLocaleString()} paid to BabyShopKE</strong>"</li>
              <li>This page updates automatically</li>
            </ol>
          </div>

          <p className="text-xs text-muted-foreground">
            Didn't get the prompt?{" "}
            <button type="button" onClick={() => { stopPolling(); setPayStatus("idle"); }}
              className="text-primary font-semibold underline">
              Go back and retry
            </button>
          </p>
        </div>
      </UserPageLayout>
    );
  }

  // ── Main checkout form (idle | sending) ───────────────────────────────────
  return (
    <UserPageLayout title="Checkout" description="Pay securely with M-Pesa.">
      <div className="grid lg:grid-cols-[1fr_340px] gap-6 max-w-4xl">

        {/* ── Left: form ─────────────────────────────────────────────────── */}
        <form onSubmit={handleSubmit} className="space-y-6">

          {/* Delivery details card */}
          <div className="bg-card rounded-2xl border border-border p-6 shadow-soft">
            <h2 className="font-extrabold text-base mb-5 flex items-center gap-2">
              <Truck className="w-5 h-5 text-primary" /> Delivery Details
            </h2>

            <div className="space-y-4">
              <div>
                <label className="block text-sm font-semibold mb-1.5">Full Name *</label>
                <div className="flex items-center gap-2 rounded-xl border border-border bg-secondary px-4 focus-within:border-primary/60 focus-within:ring-2 focus-within:ring-primary/20">
                  <User className="w-4 h-4 text-muted-foreground shrink-0" />
                  <input value={fullName} onChange={(e) => setFullName(e.target.value)}
                    className="w-full bg-transparent py-3 text-sm outline-none placeholder:text-muted-foreground"
                    placeholder="Jane Wanjiku" required />
                </div>
              </div>

              <div>
                <label className="block text-sm font-semibold mb-1.5">Contact Phone *</label>
                <div className="flex items-center gap-2 rounded-xl border border-border bg-secondary px-4 focus-within:border-primary/60 focus-within:ring-2 focus-within:ring-primary/20">
                  <Phone className="w-4 h-4 text-muted-foreground shrink-0" />
                  <input value={phone} onChange={(e) => setPhone(formatPhone(e.target.value))}
                    className="w-full bg-transparent py-3 text-sm outline-none placeholder:text-muted-foreground"
                    placeholder="0712345678" required />
                </div>
              </div>

              {/* Delivery vs Pickup */}
              <div>
                <label className="block text-sm font-semibold mb-2">Delivery Option *</label>
                <div className="grid grid-cols-2 gap-3">
                  {(["delivery", "pickup"] as const).map((opt) => (
                    <button key={opt} type="button" onClick={() => setDeliveryOpt(opt)}
                      className={`flex items-center gap-2 rounded-xl border p-3 text-sm font-semibold transition-colors ${
                        deliveryOpt === opt
                          ? "border-primary bg-primary/10 text-primary"
                          : "border-border bg-secondary text-foreground"
                      }`}>
                      {opt === "delivery" ? <Truck className="w-4 h-4" /> : <MapPin className="w-4 h-4" />}
                      <span className="capitalize">{opt}</span>
                      <span className={`ml-auto text-xs ${opt === "delivery" ? "text-muted-foreground" : "text-emerald-600"}`}>
                        {opt === "delivery" ? `+KSH ${DELIVERY_FEE}` : "Free"}
                      </span>
                    </button>
                  ))}
                </div>
              </div>

              {deliveryOpt === "delivery" && (
                <div>
                  <label className="block text-sm font-semibold mb-1.5">Delivery Address *</label>
                  <div className="flex items-start gap-2 rounded-xl border border-border bg-secondary px-4 pt-3 focus-within:border-primary/60 focus-within:ring-2 focus-within:ring-primary/20">
                    <MapPin className="w-4 h-4 text-muted-foreground shrink-0 mt-0.5" />
                    <textarea value={address} onChange={(e) => setAddress(e.target.value)} rows={3}
                      className="w-full bg-transparent pb-3 text-sm outline-none placeholder:text-muted-foreground resize-none"
                      placeholder="e.g. Westlands, Nairobi, near Junction Mall" required />
                  </div>
                </div>
              )}
            </div>
          </div>

          {/* M-Pesa payment card */}
          <div className="bg-card rounded-2xl border border-border p-6 shadow-soft">
            <h2 className="font-extrabold text-base mb-4 flex items-center gap-2">
              <Smartphone className="w-5 h-5 text-green-600" /> M-Pesa Payment
            </h2>

            <div className="bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800 mb-4">
              <p className="font-bold mb-1">How M-Pesa STK Push works:</p>
              <ol className="list-decimal list-inside space-y-1 text-green-700">
                <li>Enter your Safaricom number below</li>
                <li>Click <strong>"Pay Now"</strong> — a prompt appears on your phone</li>
                <li>Enter your <strong>M-Pesa PIN</strong> to complete payment</li>
                <li>Your order is confirmed instantly</li>
              </ol>
            </div>

            <div>
              <label className="block text-sm font-semibold mb-1.5">Safaricom M-Pesa Number *</label>
              <div className="flex items-center gap-2 rounded-xl border border-green-300 bg-secondary px-4 focus-within:border-green-500 focus-within:ring-2 focus-within:ring-green-400/25">
                <Smartphone className="w-4 h-4 text-green-600 shrink-0" />
                <input value={mpesaPhone} onChange={(e) => setMpesaPhone(formatPhone(e.target.value))}
                  className="w-full bg-transparent py-3 text-sm outline-none placeholder:text-muted-foreground"
                  placeholder="0712345678" required />
              </div>
              <p className="text-xs text-muted-foreground mt-1">
                Must be a registered Safaricom line with M-Pesa active.
              </p>
            </div>
          </div>

          {/* Submit */}
          <button type="submit" disabled={payStatus === "sending"}
            className="w-full py-4 rounded-full bg-green-600 hover:bg-green-700 text-white font-bold text-base disabled:opacity-60 flex items-center justify-center gap-2 transition-colors">
            {payStatus === "sending" ? (
              <><Loader2 className="w-5 h-5 animate-spin" /> Sending STK Push…</>
            ) : (
              <><Smartphone className="w-5 h-5" /> Pay KSH {orderTotal.toLocaleString()} with M-Pesa</>
            )}
          </button>
        </form>

        {/* ── Right: order summary ──────────────────────────────────────── */}
        <div className="bg-card rounded-2xl border border-border p-5 shadow-soft h-fit">
          <h3 className="font-extrabold text-base mb-4">Order Summary</h3>
          <div className="space-y-3 max-h-72 overflow-y-auto pr-1 mb-4">
            {cartItems.length === 0 ? (
              <p className="text-sm text-muted-foreground">Your cart is empty.</p>
            ) : (
              cartItems.map((item) => (
                <div key={item.id} className="flex items-center gap-3">
                  <img src={item.image} alt={item.name}
                    className="w-12 h-12 rounded-lg bg-secondary object-contain p-1 shrink-0" />
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-semibold truncate">{item.name}</p>
                    <p className="text-xs text-muted-foreground">Qty: {item.qty}</p>
                  </div>
                  <p className="text-sm font-bold shrink-0">
                    KSH {(item.price * item.qty).toLocaleString()}
                  </p>
                </div>
              ))
            )}
          </div>
          <div className="border-t border-border pt-4 space-y-2 text-sm">
            <div className="flex justify-between">
              <span className="text-muted-foreground">Subtotal</span>
              <span className="font-semibold">KSH {cartTotal.toLocaleString()}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-muted-foreground">Delivery</span>
              <span className={`font-semibold ${deliveryOpt === "pickup" ? "text-emerald-600" : ""}`}>
                {deliveryOpt === "pickup" ? "Free" : `KSH ${DELIVERY_FEE}`}
              </span>
            </div>
            <div className="flex justify-between font-extrabold text-base border-t border-border pt-2">
              <span>Total</span>
              <span className="text-primary">KSH {orderTotal.toLocaleString()}</span>
            </div>
          </div>

          <div className="mt-4 flex items-center gap-2 text-xs text-muted-foreground bg-secondary rounded-xl p-3">
            <Smartphone className="w-4 h-4 text-green-600 shrink-0" />
            <span>Payments secured by <strong className="text-foreground">Safaricom M-Pesa Daraja API</strong></span>
          </div>
        </div>
      </div>
    </UserPageLayout>
  );
};

export default Checkout;
