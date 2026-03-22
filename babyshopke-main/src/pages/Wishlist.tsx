import { useMemo, useState } from "react";
import { Link } from "react-router-dom";
import { toast } from "sonner";
import UserPageLayout from "@/components/UserPageLayout";
import productTeddy from "@/assets/product-teddy.png";
import productOnesie from "@/assets/product-onesie.png";

type WishlistItem = {
  id: number;
  name: string;
  price: number;
  image: string;
};

const Wishlist = () => {
  const [items, setItems] = useState<WishlistItem[]>([
    { id: 1, name: "Cozy Teddy Bear", price: 1900, image: productTeddy },
    { id: 2, name: "Cute Baby Onesie", price: 900, image: productOnesie },
  ]);

  const cartCount = useMemo(() => items.length, [items.length]);

  const removeItem = (itemId: number) => {
    setItems((prev) => prev.filter((item) => item.id !== itemId));
    toast.success("Removed from wishlist.");
  };

  const addToCart = (itemName: string) => {
    toast.success(`${itemName} added to cart.`);
  };

  return (
    <UserPageLayout
      title="Wishlist"
      description="Saved items you can add to cart anytime."
      cartCount={cartCount}
    >
      {items.length === 0 ? (
        <div className="bg-card rounded-2xl border border-border p-8 shadow-soft">
          <p className="text-muted-foreground mb-4">Your wishlist is empty.</p>
          <Link to="/shop" className="px-6 py-2.5 rounded-full bg-primary text-primary-foreground font-bold">
            Continue Shopping
          </Link>
        </div>
      ) : (
        <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
          {items.map((item) => (
            <article key={item.id} className="bg-card rounded-2xl border border-border p-4 shadow-soft">
              <img
                src={item.image}
                alt={item.name}
                className="w-full aspect-square object-contain bg-secondary rounded-xl p-3 mb-3"
              />
              <h3 className="font-bold text-foreground">{item.name}</h3>
              <p className="text-primary font-extrabold my-2">KSH {item.price.toLocaleString()}</p>
              <div className="flex gap-2">
                <button
                  type="button"
                  onClick={() => removeItem(item.id)}
                  className="flex-1 px-4 py-2 rounded-full border border-border font-semibold"
                >
                  Remove
                </button>
                <button
                  type="button"
                  onClick={() => addToCart(item.name)}
                  className="flex-1 px-4 py-2 rounded-full bg-accent text-accent-foreground font-bold"
                >
                  Add to Cart
                </button>
              </div>
            </article>
          ))}
        </div>
      )}
    </UserPageLayout>
  );
};

export default Wishlist;
