import { useMemo, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { toast } from "sonner";
import UserPageLayout from "@/components/UserPageLayout";
import productTeddy from "@/assets/product-teddy.png";
import productPhone from "@/assets/product-phone.png";

type CartItem = {
  id: number;
  name: string;
  price: number;
  qty: number;
  image: string;
};

const Cart = () => {
  const navigate = useNavigate();
  const [items, setItems] = useState<CartItem[]>([
    { id: 1, name: "Cozy Teddy Bear", price: 1900, qty: 1, image: productTeddy },
    { id: 2, name: "Musical Phone Toy", price: 1500, qty: 2, image: productPhone },
  ]);

  const cartCount = useMemo(
    () => items.reduce((total, item) => total + item.qty, 0),
    [items],
  );
  const total = useMemo(
    () => items.reduce((sum, item) => sum + item.price * item.qty, 0),
    [items],
  );

  const increaseQty = (itemId: number) => {
    setItems((prev) =>
      prev.map((item) => (item.id === itemId ? { ...item, qty: item.qty + 1 } : item)),
    );
  };

  const decreaseQty = (itemId: number) => {
    setItems((prev) =>
      prev.map((item) =>
        item.id === itemId ? { ...item, qty: item.qty > 1 ? item.qty - 1 : 1 } : item,
      ),
    );
  };

  const removeItem = (itemId: number) => {
    setItems((prev) => prev.filter((item) => item.id !== itemId));
    toast.success("Item removed.");
  };

  const clearCart = () => {
    setItems([]);
    toast.success("Cart cleared.");
  };

  return (
    <UserPageLayout title="Cart" description="Review your items and proceed to checkout." cartCount={cartCount}>
      {items.length === 0 ? (
        <div className="bg-card rounded-2xl border border-border p-8 shadow-soft">
          <p className="text-muted-foreground mb-4">Your cart is empty.</p>
          <Link to="/shop" className="px-6 py-2.5 rounded-full bg-primary text-primary-foreground font-bold">
            Start Shopping
          </Link>
        </div>
      ) : (
        <div className="space-y-4">
          {items.map((item) => (
            <article
              key={item.id}
              className="bg-card rounded-2xl border border-border p-4 shadow-soft flex flex-wrap items-center gap-4"
            >
              <img src={item.image} alt={item.name} className="w-20 h-20 object-contain bg-secondary rounded-xl p-2" />
              <div className="flex-1 min-w-[180px]">
                <h3 className="font-bold">{item.name}</h3>
                <p className="text-primary font-extrabold">KSH {item.price.toLocaleString()}</p>
              </div>
              <div className="flex items-center gap-2">
                <button
                  type="button"
                  onClick={() => decreaseQty(item.id)}
                  className="w-9 h-9 rounded-full border border-border font-bold"
                >
                  -
                </button>
                <span className="min-w-8 text-center font-bold">{item.qty}</span>
                <button
                  type="button"
                  onClick={() => increaseQty(item.id)}
                  className="w-9 h-9 rounded-full border border-border font-bold"
                >
                  +
                </button>
              </div>
              <button
                type="button"
                onClick={() => removeItem(item.id)}
                className="px-4 py-2 rounded-full border border-border text-sm font-semibold"
              >
                Remove
              </button>
            </article>
          ))}

          <div className="bg-card rounded-2xl border border-border p-5 shadow-soft flex flex-wrap items-center gap-3 justify-between">
            <p className="text-lg font-extrabold">Total: KSH {total.toLocaleString()}</p>
            <div className="flex gap-2">
              <button type="button" onClick={clearCart} className="px-5 py-2.5 rounded-full border border-border font-semibold">
                Clear Cart
              </button>
              <button
                type="button"
                onClick={() => navigate("/checkout")}
                className="px-5 py-2.5 rounded-full bg-accent text-accent-foreground font-bold"
              >
                Proceed to Checkout
              </button>
            </div>
          </div>
        </div>
      )}
    </UserPageLayout>
  );
};

export default Cart;
