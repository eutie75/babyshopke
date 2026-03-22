import { Minus, Plus, ShoppingBag, Trash2 } from "lucide-react";
import { useNavigate } from "react-router-dom";
import { toast } from "sonner";
import { useCommerce } from "@/context/CommerceContext";
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
} from "@/components/ui/sheet";

const CommerceDrawers = () => {
  const navigate = useNavigate();
  const {
    wishlistItems,
    cartItems,
    cartTotal,
    isWishlistOpen,
    isCartOpen,
    openWishlist,
    closeWishlist,
    openCart,
    closeCart,
    removeFromWishlist,
    moveWishlistItemToCart,
    removeFromCart,
    increaseQty,
    decreaseQty,
    clearCart,
  } = useCommerce();

  const openWishlistDrawer = (open: boolean) => {
    if (open) {
      openWishlist();
      return;
    }
    closeWishlist();
  };

  const openCartDrawer = (open: boolean) => {
    if (open) {
      openCart();
      return;
    }
    closeCart();
  };

  const proceedToCheckout = () => {
    closeCart();
    navigate("/checkout");
  };

  return (
    <>
      <Sheet open={isWishlistOpen} onOpenChange={openWishlistDrawer}>
        <SheetContent side="right" className="w-full sm:max-w-md bg-card/95 backdrop-blur-xl">
          <SheetHeader className="pr-8">
            <SheetTitle>Wishlist</SheetTitle>
            <SheetDescription>Items you saved for later.</SheetDescription>
          </SheetHeader>

          <div className="mt-5 space-y-3 max-h-[68vh] overflow-y-auto pr-1">
            {wishlistItems.length === 0 ? (
              <div className="rounded-2xl border border-border bg-secondary/70 p-5 text-center">
                <p className="text-sm text-muted-foreground">Your wishlist is empty.</p>
                <button
                  type="button"
                  onClick={() => {
                    closeWishlist();
                    navigate("/shop");
                  }}
                  className="mt-3 rounded-full bg-primary px-4 py-2 text-sm font-bold text-primary-foreground"
                >
                  Start Shopping
                </button>
              </div>
            ) : (
              wishlistItems.map((item) => (
                <article key={item.id} className="rounded-2xl border border-border bg-card p-3 shadow-soft">
                  <div className="flex items-center gap-3">
                    <img
                      src={item.image}
                      alt={item.name}
                      className="h-16 w-16 rounded-xl bg-secondary object-contain p-1.5"
                    />
                    <div className="min-w-0 flex-1">
                      <h3 className="truncate font-bold">{item.name}</h3>
                      <p className="text-sm font-semibold text-primary">
                        KSH {item.price.toLocaleString()}
                      </p>
                    </div>
                  </div>

                  <div className="mt-3 flex gap-2">
                    <button
                      type="button"
                      onClick={() => {
                        moveWishlistItemToCart(item.id);
                        toast.success(`${item.name} added to cart.`);
                      }}
                      className="flex-1 rounded-full bg-accent px-3 py-2 text-sm font-bold text-accent-foreground"
                    >
                      Add to Cart
                    </button>
                    <button
                      type="button"
                      onClick={() => {
                        removeFromWishlist(item.id);
                        toast.success("Removed from wishlist.");
                      }}
                      className="rounded-full border border-border px-3 py-2"
                      aria-label={`Remove ${item.name} from wishlist`}
                    >
                      <Trash2 className="h-4 w-4" />
                    </button>
                  </div>
                </article>
              ))
            )}
          </div>
        </SheetContent>
      </Sheet>

      <Sheet open={isCartOpen} onOpenChange={openCartDrawer}>
        <SheetContent side="right" className="w-full sm:max-w-md bg-card/95 backdrop-blur-xl">
          <SheetHeader className="pr-8">
            <SheetTitle>Cart</SheetTitle>
            <SheetDescription>Review your items and continue to checkout.</SheetDescription>
          </SheetHeader>

          <div className="mt-5 space-y-3 max-h-[58vh] overflow-y-auto pr-1">
            {cartItems.length === 0 ? (
              <div className="rounded-2xl border border-border bg-secondary/70 p-5 text-center">
                <p className="text-sm text-muted-foreground">Your cart is empty.</p>
                <button
                  type="button"
                  onClick={() => {
                    closeCart();
                    navigate("/shop");
                  }}
                  className="mt-3 rounded-full bg-primary px-4 py-2 text-sm font-bold text-primary-foreground"
                >
                  Browse Products
                </button>
              </div>
            ) : (
              cartItems.map((item) => (
                <article key={item.id} className="rounded-2xl border border-border bg-card p-3 shadow-soft">
                  <div className="flex items-center gap-3">
                    <img
                      src={item.image}
                      alt={item.name}
                      className="h-16 w-16 rounded-xl bg-secondary object-contain p-1.5"
                    />
                    <div className="min-w-0 flex-1">
                      <h3 className="truncate font-bold">{item.name}</h3>
                      <p className="text-sm font-semibold text-primary">
                        KSH {item.price.toLocaleString()}
                      </p>
                    </div>
                    <button
                      type="button"
                      onClick={() => {
                        removeFromCart(item.id);
                        toast.success("Item removed.");
                      }}
                      className="rounded-full border border-border p-2"
                      aria-label={`Remove ${item.name} from cart`}
                    >
                      <Trash2 className="h-4 w-4" />
                    </button>
                  </div>

                  <div className="mt-3 flex items-center justify-between">
                    <div className="inline-flex items-center gap-2 rounded-full border border-border bg-secondary/70 px-2 py-1">
                      <button
                        type="button"
                        onClick={() => decreaseQty(item.id)}
                        className="rounded-full p-1"
                        aria-label={`Decrease quantity for ${item.name}`}
                      >
                        <Minus className="h-3.5 w-3.5" />
                      </button>
                      <span className="min-w-6 text-center text-sm font-bold">{item.qty}</span>
                      <button
                        type="button"
                        onClick={() => increaseQty(item.id)}
                        className="rounded-full p-1"
                        aria-label={`Increase quantity for ${item.name}`}
                      >
                        <Plus className="h-3.5 w-3.5" />
                      </button>
                    </div>

                    <p className="text-sm font-bold">
                      KSH {(item.price * item.qty).toLocaleString()}
                    </p>
                  </div>
                </article>
              ))
            )}
          </div>

          {cartItems.length > 0 ? (
            <div className="mt-4 border-t border-border pt-4">
              <div className="mb-3 flex items-center justify-between">
                <span className="text-sm text-muted-foreground">Total</span>
                <span className="text-lg font-extrabold">
                  KSH {cartTotal.toLocaleString()}
                </span>
              </div>

              <div className="flex gap-2">
                <button
                  type="button"
                  onClick={() => {
                    clearCart();
                    toast.success("Cart cleared.");
                  }}
                  className="rounded-full border border-border px-4 py-2 text-sm font-semibold"
                >
                  Clear Cart
                </button>
                <button
                  type="button"
                  onClick={proceedToCheckout}
                  className="inline-flex flex-1 items-center justify-center gap-2 rounded-full bg-accent px-4 py-2 text-sm font-bold text-accent-foreground"
                >
                  <ShoppingBag className="h-4 w-4" />
                  Checkout
                </button>
              </div>
            </div>
          ) : null}
        </SheetContent>
      </Sheet>
    </>
  );
};

export default CommerceDrawers;
