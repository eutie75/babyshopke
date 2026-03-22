import { FormEvent, useState } from "react";
import { Search, Heart, ShoppingCart, User } from "lucide-react";
import BrandLogo from "@/components/BrandLogo";

interface HeaderProps {
  cartCount: number;
  wishlistCount?: number;
  onWishlistClick: () => void;
  onCartClick: () => void;
  onUserClick: () => void;
  onSearchSubmit: (query: string) => void;
}

const Header = ({
  cartCount,
  wishlistCount = 0,
  onWishlistClick,
  onCartClick,
  onUserClick,
  onSearchSubmit,
}: HeaderProps) => {
  const [searchQuery, setSearchQuery] = useState("");

  const handleSearchSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    onSearchSubmit(searchQuery);
  };

  return (
    <header className="w-full">
      {/* Main Nav */}
      <nav className="bg-card px-4 md:px-8 h-14 md:h-[58px] flex items-center justify-between gap-4 max-w-[1400px] mx-auto">
        {/* Logo */}
        <BrandLogo className="shrink-0 overflow-visible" imageClassName="w-9 h-9 scale-[1.45]" />

        {/* Search Bar */}
        <form onSubmit={handleSearchSubmit} className="hidden md:flex flex-1 max-w-md mx-4">
          <div className="relative w-full">
            <button
              type="submit"
              aria-label="Search products"
              className="absolute left-2.5 top-1/2 -translate-y-1/2 p-1 rounded-full text-muted-foreground hover:text-foreground transition-colors"
            >
              <Search className="w-4 h-4" />
            </button>
            <input
              type="text"
              placeholder="Search for products..."
              value={searchQuery}
              onChange={(event) => setSearchQuery(event.target.value)}
              className="w-full pl-10 pr-4 py-2.5 rounded-full border border-border bg-secondary text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 transition-all"
            />
          </div>
        </form>

        {/* Icons */}
        <div className="flex items-center gap-3 md:gap-4 shrink-0">
          <button
            type="button"
            onClick={onWishlistClick}
            aria-label="Open wishlist"
            className="p-1.5 rounded-full hover:bg-secondary transition-colors relative"
          >
            <Heart className="w-5 h-5 text-accent" />
            {wishlistCount > 0 ? (
              <span className="absolute -top-0.5 -right-0.5 w-4 h-4 rounded-full bg-primary text-primary-foreground text-[10px] font-bold flex items-center justify-center">
                {wishlistCount > 99 ? "99+" : wishlistCount}
              </span>
            ) : null}
          </button>
          <button
            type="button"
            onClick={onCartClick}
            aria-label="Open cart"
            className="p-1.5 rounded-full hover:bg-secondary transition-colors relative"
          >
            <ShoppingCart className="w-5 h-5 text-foreground" />
            <span className="absolute -top-0.5 -right-0.5 w-4 h-4 rounded-full bg-accent text-accent-foreground text-[10px] font-bold flex items-center justify-center">
              {cartCount > 99 ? "99+" : cartCount}
            </span>
          </button>
          <button
            type="button"
            onClick={onUserClick}
            aria-label="Open account"
            className="p-1.5 rounded-full hover:bg-secondary transition-colors"
          >
            <User className="w-5 h-5 text-foreground" />
          </button>
        </div>
      </nav>

      {/* Announcement Bar */}
      <div className="bg-primary text-primary-foreground text-center py-2 text-sm font-semibold tracking-wide">
        🚚 Free Shipping on orders over KSH 5,000 🇰🇪
      </div>
    </header>
  );
};

export default Header;
