import { useMemo } from "react";
import { useSearchParams } from "react-router-dom";
import { Heart, Star } from "lucide-react";
import { toast } from "sonner";
import UserPageLayout from "@/components/UserPageLayout";
import { useCommerce } from "@/context/CommerceContext";
import productTeddy from "@/assets/product-teddy.png";
import productPhone from "@/assets/product-phone.png";
import productOnesie from "@/assets/product-onesie.png";
import productStacking from "@/assets/product-stacking.png";

type Product = {
  id: number;
  name: string;
  price: number;
  rating: number;
  image: string;
  category: "Diapers & Wipes" | "Feeding" | "Toys" | "Clothing";
  ageMinMonths: number;
  ageMaxMonths: number;
};

const categories = ["All", "Diapers & Wipes", "Feeding", "Toys", "Clothing"] as const;

const ageTabs = [
  { value: "all", label: "All Ages", min: 0, max: Number.POSITIVE_INFINITY },
  { value: "0-3", label: "0-3 mo", min: 0, max: 3 },
  { value: "3-6", label: "3-6 mo", min: 3, max: 6 },
  { value: "6-12", label: "6-12 mo", min: 6, max: 12 },
  { value: "12-18", label: "12-18 mo", min: 12, max: 18 },
  { value: "24-48", label: "24-48 mo", min: 24, max: 48 },
] as const;

const products: Product[] = [
  {
    id: 1,
    name: "Sensitive Baby Wipes",
    price: 350,
    rating: 4.6,
    image: productStacking,
    category: "Diapers & Wipes",
    ageMinMonths: 0,
    ageMaxMonths: 36,
  },
  {
    id: 2,
    name: "Ultra Dry Diapers Pack",
    price: 1500,
    rating: 4.8,
    image: productStacking,
    category: "Diapers & Wipes",
    ageMinMonths: 0,
    ageMaxMonths: 24,
  },
  {
    id: 3,
    name: "Silicone Feeding Set",
    price: 1800,
    rating: 4.7,
    image: productPhone,
    category: "Feeding",
    ageMinMonths: 6,
    ageMaxMonths: 24,
  },
  {
    id: 4,
    name: "Baby Bottle 250ml",
    price: 650,
    rating: 4.4,
    image: productPhone,
    category: "Feeding",
    ageMinMonths: 0,
    ageMaxMonths: 12,
  },
  {
    id: 5,
    name: "Musical Phone Toy",
    price: 1500,
    rating: 4.5,
    image: productPhone,
    category: "Toys",
    ageMinMonths: 3,
    ageMaxMonths: 18,
  },
  {
    id: 6,
    name: "Interactive Stacking Cups",
    price: 1200,
    rating: 4.5,
    image: productStacking,
    category: "Toys",
    ageMinMonths: 6,
    ageMaxMonths: 24,
  },
  {
    id: 7,
    name: "Cozy Teddy Bear",
    price: 1900,
    rating: 5,
    image: productTeddy,
    category: "Toys",
    ageMinMonths: 6,
    ageMaxMonths: 48,
  },
  {
    id: 8,
    name: "Newborn Onesie Set",
    price: 900,
    rating: 4.8,
    image: productOnesie,
    category: "Clothing",
    ageMinMonths: 0,
    ageMaxMonths: 6,
  },
  {
    id: 9,
    name: "Cotton Sleep Suit",
    price: 1200,
    rating: 4.6,
    image: productOnesie,
    category: "Clothing",
    ageMinMonths: 3,
    ageMaxMonths: 18,
  },
  {
    id: 10,
    name: "Warm Hoodie Set",
    price: 2200,
    rating: 4.4,
    image: productOnesie,
    category: "Clothing",
    ageMinMonths: 12,
    ageMaxMonths: 48,
  },
  {
    id: 11,
    name: "Soft Teether Ring",
    price: 540,
    rating: 4.3,
    image: productTeddy,
    category: "Toys",
    ageMinMonths: 3,
    ageMaxMonths: 12,
  },
  {
    id: 12,
    name: "Bib and Spoon Combo",
    price: 780,
    rating: 4.2,
    image: productPhone,
    category: "Feeding",
    ageMinMonths: 6,
    ageMaxMonths: 24,
  },
];

const Shop = () => {
  const [searchParams, setSearchParams] = useSearchParams();
  const {
    addToCart,
    openCart,
    openWishlist,
    toggleWishlist,
    isWishlisted,
  } = useCommerce();

  const query = (searchParams.get("q") ?? "").trim();
  const normalizedQuery = query.toLowerCase();
  const activeCategory =
    categories.find((category) => category === (searchParams.get("cat") ?? "")) ?? "All";
  const activeAge = ageTabs.find((tab) => tab.value === searchParams.get("age")) ?? ageTabs[0];

  const filteredProducts = useMemo(() => {
    return products.filter((product) => {
      const matchesQuery =
        normalizedQuery.length === 0 ||
        product.name.toLowerCase().includes(normalizedQuery) ||
        product.category.toLowerCase().includes(normalizedQuery);

      const matchesCategory = activeCategory === "All" || product.category === activeCategory;
      const matchesAge =
        activeAge.value === "all" ||
        (product.ageMinMonths <= activeAge.max && product.ageMaxMonths >= activeAge.min);

      return matchesQuery && matchesCategory && matchesAge;
    });
  }, [activeAge.max, activeAge.min, activeAge.value, activeCategory, normalizedQuery]);

  const setFilter = (key: "q" | "cat" | "age", value: string) => {
    const next = new URLSearchParams(searchParams);

    if (!value || value === "all" || value === "All") {
      next.delete(key);
    } else {
      next.set(key, value);
    }

    setSearchParams(next);
  };

  const toggleWishlistItem = (product: Product) => {
    const added = toggleWishlist(product);
    toast.success(
      added
        ? `${product.name} added to wishlist.`
        : `${product.name} removed from wishlist.`,
    );
  };

  const addToCartItem = (product: Product) => {
    addToCart(product);
    toast.success(`${product.name} added to cart.`);
  };

  return (
    <UserPageLayout title="Shop" description="Browse products by age, category, or search and add favorites to cart.">
      <section className="bg-card rounded-2xl border border-border p-4 md:p-5 shadow-soft mb-6">
        <div className="flex flex-wrap gap-2">
          {categories.map((category) => (
            <button
              key={category}
              type="button"
              onClick={() => setFilter("cat", category)}
              className={`px-4 py-2 rounded-full text-sm font-semibold transition-colors ${
                activeCategory === category
                  ? "bg-primary text-primary-foreground"
                  : "bg-secondary border border-border text-foreground"
              }`}
            >
              {category}
            </button>
          ))}
        </div>

        <div className="flex flex-wrap gap-2 mt-3">
          {ageTabs.map((tab) => (
            <button
              key={tab.value}
              type="button"
              onClick={() => setFilter("age", tab.value)}
              className={`px-4 py-2 rounded-full text-sm font-semibold transition-colors ${
                activeAge.value === tab.value
                  ? "bg-accent text-accent-foreground"
                  : "bg-secondary border border-border text-foreground"
              }`}
            >
              {tab.label}
            </button>
          ))}
        </div>

        <div className="mt-3 text-sm text-muted-foreground">
          <span className="font-semibold text-foreground">Search:</span>{" "}
          {query || "All products"}
        </div>
      </section>

      {filteredProducts.length === 0 ? (
        <div className="bg-card rounded-2xl border border-border p-6 shadow-soft">
          <p className="text-muted-foreground mb-4">
            No products match your current filters.
          </p>
          <button
            type="button"
            onClick={() => {
              setSearchParams({});
              toast.success("Filters cleared.");
            }}
            className="px-5 py-2.5 rounded-full bg-primary text-primary-foreground font-bold"
          >
            Clear Filters
          </button>
        </div>
      ) : (
        <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
          {filteredProducts.map((product) => {
            const wishlisted = isWishlisted(product.id);

            return (
              <article
                key={product.id}
                className="bg-card rounded-2xl border border-border p-4 shadow-soft"
              >
                <div className="relative bg-secondary rounded-xl p-3 mb-3">
                  <img
                    src={product.image}
                    alt={product.name}
                    className="w-full aspect-square object-contain"
                  />
                  <button
                    type="button"
                    onClick={() => toggleWishlistItem(product)}
                    className="absolute top-2.5 right-2.5 p-1.5 rounded-full bg-card/90 border border-border"
                    aria-label={wishlisted ? "Remove from wishlist" : "Add to wishlist"}
                  >
                    <Heart
                      className={`w-4 h-4 ${
                        wishlisted
                          ? "text-accent fill-accent"
                          : "text-muted-foreground"
                      }`}
                    />
                  </button>
                </div>

                <h3 className="font-bold text-foreground">{product.name}</h3>
                <p className="text-xs text-primary font-semibold">{product.category}</p>

                <div className="mt-1 flex items-center gap-1">
                  {Array.from({ length: 5 }).map((_, index) => (
                    <Star
                      key={index}
                      className={`w-3.5 h-3.5 ${
                        index < Math.floor(product.rating)
                          ? "text-amber-400 fill-amber-400"
                          : "text-border"
                      }`}
                    />
                  ))}
                </div>

                <p className="mt-2 mb-3 font-extrabold text-foreground">
                  KSH {product.price.toLocaleString()}
                </p>

                <button
                  type="button"
                  onClick={() => addToCartItem(product)}
                  className="w-full py-2.5 rounded-full bg-accent text-accent-foreground font-bold"
                >
                  Add to Cart
                </button>
              </article>
            );
          })}
        </div>
      )}

      <div className="mt-6 flex flex-wrap gap-2">
        <button
          type="button"
          onClick={openCart}
          className="px-5 py-2.5 rounded-full bg-primary text-primary-foreground font-bold"
        >
          Open Cart Drawer
        </button>
        <button
          type="button"
          onClick={openWishlist}
          className="px-5 py-2.5 rounded-full border border-border bg-card font-semibold"
        >
          Open Wishlist Drawer
        </button>
      </div>
    </UserPageLayout>
  );
};

export default Shop;
