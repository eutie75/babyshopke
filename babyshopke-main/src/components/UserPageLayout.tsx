import { ReactNode } from "react";
import { useNavigate } from "react-router-dom";
import Header from "@/components/Header";
import { useCommerce } from "@/context/CommerceContext";

interface UserPageLayoutProps {
  title: string;
  description?: string;
  children: ReactNode;
  cartCount?: number;
}

const UserPageLayout = ({ title, description, children, cartCount = 0 }: UserPageLayoutProps) => {
  const navigate = useNavigate();
  const { cartCount: globalCartCount, wishlistCount, openCart, openWishlist } = useCommerce();

  return (
    <div className="min-h-screen bg-background">
      <Header
        cartCount={cartCount || globalCartCount}
        wishlistCount={wishlistCount}
        onWishlistClick={openWishlist}
        onCartClick={openCart}
        onUserClick={() => navigate("/account")}
        onSearchSubmit={(query) =>
          navigate(query.trim() ? `/shop?q=${encodeURIComponent(query.trim())}` : "/shop")
        }
      />
      <main className="max-w-5xl mx-auto px-4 md:px-8 py-10">
        <div className="mb-6">
          <h1 className="text-3xl md:text-4xl font-extrabold text-foreground">{title}</h1>
          {description ? (
            <p className="text-muted-foreground mt-2">{description}</p>
          ) : null}
        </div>
        {children}
      </main>
    </div>
  );
};

export default UserPageLayout;
