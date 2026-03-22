import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { toast } from "sonner";
import Header from "@/components/Header";
import HeroSection from "@/components/HeroSection";
import CategorySection from "@/components/CategorySection";
import AgeFilterSection from "@/components/AgeFilterSection";
import BottomCards from "@/components/BottomCards";
import { CommerceProduct, useCommerce } from "@/context/CommerceContext";
import productTeddy from "@/assets/product-teddy.png";
import productPhone from "@/assets/product-phone.png";
import productOnesie from "@/assets/product-onesie.png";
import productStacking from "@/assets/product-stacking.png";

const featuredProductsByName: Record<string, CommerceProduct> = {
  "Cozy Teddy Bear": {
    id: 7,
    name: "Cozy Teddy Bear",
    price: 1900,
    image: productTeddy,
  },
  "Musical Phone Toy": {
    id: 5,
    name: "Musical Phone Toy",
    price: 1500,
    image: productPhone,
  },
  "Cute Baby Onesie": {
    id: 108,
    name: "Cute Baby Onesie",
    price: 900,
    image: productOnesie,
  },
  "Interactive Stacking Cups": {
    id: 6,
    name: "Interactive Stacking Cups",
    price: 1200,
    image: productStacking,
  },
};

const Index = () => {
  const navigate = useNavigate();
  const [selectedCategory, setSelectedCategory] = useState<string | null>(null);
  const [searchQuery, setSearchQuery] = useState("");
  const {
    cartCount,
    wishlistCount,
    addToCart,
    addToWishlist,
    removeFromWishlist,
    isWishlistedByName,
    openWishlist,
    openCart,
  } = useCommerce();

  const scrollTo = (sectionId: string) => {
    const section = document.getElementById(sectionId);
    if (section) {
      section.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  };

  const handleShopNow = () => {
    navigate("/shop");
  };

  const handleCategorySelect = (category: string) => {
    setSelectedCategory((prev) => {
      const next = prev === category ? null : category;
      toast(next ? `Filtering by ${next}` : "Category filter cleared");
      return next;
    });
    scrollTo("products");
  };

  const handleWishlistToggle = (productName: string, nextState: boolean) => {
    const product = featuredProductsByName[productName];
    if (!product) {
      return;
    }

    if (nextState) {
      addToWishlist(product);
      toast(`${productName} added to wishlist`);
      return;
    }

    removeFromWishlist(product.id);
    toast(`${productName} removed from wishlist`);
  };

  const handleAddToCart = (productName: string) => {
    const product = featuredProductsByName[productName];
    if (!product) {
      return;
    }

    addToCart(product);
    toast(`${productName} added to cart`);
  };

  const handleSearchSubmit = (query: string) => {
    const trimmedQuery = query.trim();
    setSearchQuery(trimmedQuery);
    scrollTo("products");
    toast(trimmedQuery ? `Showing results for "${trimmedQuery}"` : "Showing all products");
  };

  const handleWishlistClick = () => {
    openWishlist();
  };

  const handleCartClick = () => {
    openCart();
  };

  const handleUserClick = () => {
    navigate("/account");
  };

  const handleGetStarted = () => {
    navigate("/get-started");
  };

  const featuredWishlist = new Set(
    Object.keys(featuredProductsByName).filter((productName) =>
      isWishlistedByName(productName),
    ),
  );

  return (
    <div className="min-h-screen bg-background">
      <Header
        cartCount={cartCount}
        wishlistCount={wishlistCount}
        onWishlistClick={handleWishlistClick}
        onCartClick={handleCartClick}
        onUserClick={handleUserClick}
        onSearchSubmit={handleSearchSubmit}
      />
      <HeroSection onShopNow={handleShopNow} />
      <CategorySection activeCategory={selectedCategory} onCategorySelect={handleCategorySelect} />
      <AgeFilterSection
        selectedCategory={selectedCategory}
        searchQuery={searchQuery}
        wishlist={featuredWishlist}
        onWishlistToggle={handleWishlistToggle}
        onAddToCart={handleAddToCart}
      />
      <BottomCards onGetStarted={handleGetStarted} />
    </div>
  );
};

export default Index;
