import { useState } from "react";
import { Star, Heart } from "lucide-react";
import productTeddy from "@/assets/product-teddy.png";
import productPhone from "@/assets/product-phone.png";
import productOnesie from "@/assets/product-onesie.png";
import productStacking from "@/assets/product-stacking.png";

const ageTabs = ["0–3 mo", "3–6 mo", "6–12 mo", "12–18 mo", "2–4 yr"];

interface Product {
  name: string;
  price: number;
  rating: number;
  image: string;
  category: string;
  ages: string[];
}

const products = [
  {
    name: "Cozy Teddy Bear",
    price: 1900,
    rating: 5,
    image: productTeddy,
    category: "Toys",
    ages: ["6–12 mo", "12–18 mo", "2–4 yr"],
  },
  {
    name: "Musical Phone Toy",
    price: 1500,
    rating: 4.5,
    image: productPhone,
    category: "Feeding",
    ages: ["3–6 mo", "6–12 mo", "12–18 mo"],
  },
  {
    name: "Cute Baby Onesie",
    price: 900,
    rating: 5,
    image: productOnesie,
    category: "Clothing",
    ages: ["0–3 mo", "3–6 mo", "6–12 mo"],
  },
  {
    name: "Interactive Stacking Cups",
    price: 1200,
    rating: 4.5,
    image: productStacking,
    category: "Diapers & Wipes",
    ages: ["6–12 mo", "12–18 mo"],
  },
] satisfies Product[];

interface AgeFilterSectionProps {
  selectedCategory: string | null;
  searchQuery: string;
  wishlist: Set<string>;
  onWishlistToggle: (productName: string, nextState: boolean) => void;
  onAddToCart: (productName: string) => void;
}

const AgeFilterSection = ({
  selectedCategory,
  searchQuery,
  wishlist,
  onWishlistToggle,
  onAddToCart,
}: AgeFilterSectionProps) => {
  const [activeTab, setActiveTab] = useState(3);
  const selectedAge = ageTabs[activeTab];
  const normalizedSearch = searchQuery.trim().toLowerCase();

  const filteredProducts = products.filter((product) => {
    const matchesAge = product.ages.includes(selectedAge);
    const matchesCategory = !selectedCategory || product.category === selectedCategory;
    const matchesSearch =
      !normalizedSearch ||
      product.name.toLowerCase().includes(normalizedSearch) ||
      product.category.toLowerCase().includes(normalizedSearch);

    return matchesAge && matchesCategory && matchesSearch;
  });

  return (
    <section id="products" className="max-w-[1400px] mx-auto px-4 md:px-8 py-12 md:py-16">
      {/* Heading */}
      <h2 className="text-2xl md:text-3xl font-extrabold text-center text-foreground mb-6">
        ✨ Top Picks for{" "}
        <span className="text-primary">{selectedAge}</span>
        {" "}✨
      </h2>
      <p className="text-center text-sm text-muted-foreground mb-8">
        Category: {selectedCategory ?? "All"}
        {searchQuery ? ` | Search: "${searchQuery}"` : ""}
      </p>

      {/* Tabs */}
      <div className="flex justify-center gap-2 mb-10 flex-wrap">
        {ageTabs.map((tab, i) => (
          <button
            key={tab}
            type="button"
            onClick={() => setActiveTab(i)}
            className={`px-5 py-2 rounded-full text-sm font-semibold transition-all duration-200 ${
              i === activeTab
                ? "bg-primary text-primary-foreground shadow-glow-primary"
                : "bg-card text-foreground border border-border hover:border-primary/40"
            }`}
          >
            {tab}
          </button>
        ))}
      </div>

      {/* Product Grid */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
        {filteredProducts.map((product) => {
          const wishlisted = wishlist.has(product.name);

          return (
          <div
            key={product.name}
            className="bg-card rounded-2xl shadow-soft hover:shadow-card transition-all duration-200 overflow-hidden group"
          >
            {/* Image */}
            <div className="relative p-4 bg-secondary">
              <img
                src={product.image}
                alt={product.name}
                className="w-full aspect-square object-contain group-hover:scale-105 transition-transform duration-300"
              />
              <button
                type="button"
                onClick={() => onWishlistToggle(product.name, !wishlisted)}
                className="absolute top-3 right-3 p-1.5 rounded-full bg-card/80 hover:bg-card shadow-soft transition-colors"
              >
                <Heart
                  className={`w-4 h-4 transition-colors ${
                    wishlisted ? "text-accent fill-accent" : "text-muted-foreground hover:text-accent"
                  }`}
                />
              </button>
            </div>

            {/* Info */}
            <div className="p-4 space-y-2">
              <h3 className="font-bold text-foreground text-sm leading-tight">{product.name}</h3>
              <p className="text-xs font-semibold text-primary">{product.category}</p>
              <div className="flex items-center gap-0.5">
                {Array.from({ length: 5 }).map((_, i) => (
                  <Star
                    key={i}
                    className={`w-3.5 h-3.5 ${
                      i < Math.floor(product.rating)
                        ? "text-amber-400 fill-amber-400"
                        : i < product.rating
                        ? "text-amber-400 fill-amber-400/50"
                        : "text-border"
                    }`}
                  />
                ))}
              </div>
              <p className="font-extrabold text-foreground">
                KSH {product.price.toLocaleString()}
              </p>
              <button
                type="button"
                onClick={() => onAddToCart(product.name)}
                className="w-full py-2 rounded-full bg-accent text-accent-foreground text-sm font-bold hover:brightness-105 hover:shadow-glow-accent transition-all duration-200"
              >
                Add to Cart
              </button>
            </div>
          </div>
          );
        })}
      </div>

      {filteredProducts.length === 0 ? (
        <p className="text-center text-sm text-muted-foreground mt-8">
          No products match this age, category, and search combination.
        </p>
      ) : null}
    </section>
  );
};

export default AgeFilterSection;
