import { ReactNode, createContext, useContext, useMemo, useState } from "react";

export type CommerceProduct = {
  id: number;
  name: string;
  price: number;
  image: string;
};

export type CartProduct = CommerceProduct & {
  qty: number;
};

type CommerceContextValue = {
  wishlistItems: CommerceProduct[];
  cartItems: CartProduct[];
  wishlistCount: number;
  cartCount: number;
  cartTotal: number;
  isWishlistOpen: boolean;
  isCartOpen: boolean;
  openWishlist: () => void;
  closeWishlist: () => void;
  openCart: () => void;
  closeCart: () => void;
  addToWishlist: (product: CommerceProduct) => void;
  removeFromWishlist: (productId: number) => void;
  toggleWishlist: (product: CommerceProduct) => boolean;
  isWishlisted: (productId: number) => boolean;
  isWishlistedByName: (productName: string) => boolean;
  addToCart: (product: CommerceProduct, qty?: number) => void;
  removeFromCart: (productId: number) => void;
  increaseQty: (productId: number) => void;
  decreaseQty: (productId: number) => void;
  clearCart: () => void;
  moveWishlistItemToCart: (productId: number) => void;
};

const CommerceContext = createContext<CommerceContextValue | null>(null);

export const CommerceProvider = ({ children }: { children: ReactNode }) => {
  const [wishlistItems, setWishlistItems] = useState<CommerceProduct[]>([]);
  const [cartItems, setCartItems] = useState<CartProduct[]>([]);
  const [isWishlistOpen, setIsWishlistOpen] = useState(false);
  const [isCartOpen, setIsCartOpen] = useState(false);

  const addToWishlist = (product: CommerceProduct) => {
    setWishlistItems((prev) => {
      if (prev.some((item) => item.id === product.id)) {
        return prev;
      }
      return [...prev, product];
    });
  };

  const removeFromWishlist = (productId: number) => {
    setWishlistItems((prev) => prev.filter((item) => item.id !== productId));
  };

  const toggleWishlist = (product: CommerceProduct): boolean => {
    let wasAdded = false;

    setWishlistItems((prev) => {
      const exists = prev.some((item) => item.id === product.id);
      if (exists) {
        wasAdded = false;
        return prev.filter((item) => item.id !== product.id);
      }

      wasAdded = true;
      return [...prev, product];
    });

    return wasAdded;
  };

  const addToCart = (product: CommerceProduct, qty = 1) => {
    setCartItems((prev) => {
      const existing = prev.find((item) => item.id === product.id);
      if (existing) {
        return prev.map((item) =>
          item.id === product.id ? { ...item, qty: item.qty + qty } : item,
        );
      }

      return [...prev, { ...product, qty: Math.max(1, qty) }];
    });
  };

  const removeFromCart = (productId: number) => {
    setCartItems((prev) => prev.filter((item) => item.id !== productId));
  };

  const increaseQty = (productId: number) => {
    setCartItems((prev) =>
      prev.map((item) =>
        item.id === productId ? { ...item, qty: item.qty + 1 } : item,
      ),
    );
  };

  const decreaseQty = (productId: number) => {
    setCartItems((prev) =>
      prev.map((item) =>
        item.id === productId ? { ...item, qty: item.qty > 1 ? item.qty - 1 : 1 } : item,
      ),
    );
  };

  const clearCart = () => {
    setCartItems([]);
  };

  const moveWishlistItemToCart = (productId: number) => {
    const wishlistItem = wishlistItems.find((item) => item.id === productId);
    if (!wishlistItem) {
      return;
    }

    addToCart(wishlistItem, 1);
    removeFromWishlist(productId);
  };

  const cartCount = useMemo(
    () => cartItems.reduce((total, item) => total + item.qty, 0),
    [cartItems],
  );
  const wishlistCount = useMemo(() => wishlistItems.length, [wishlistItems]);
  const cartTotal = useMemo(
    () => cartItems.reduce((sum, item) => sum + item.price * item.qty, 0),
    [cartItems],
  );

  const value: CommerceContextValue = {
    wishlistItems,
    cartItems,
    wishlistCount,
    cartCount,
    cartTotal,
    isWishlistOpen,
    isCartOpen,
    openWishlist: () => setIsWishlistOpen(true),
    closeWishlist: () => setIsWishlistOpen(false),
    openCart: () => setIsCartOpen(true),
    closeCart: () => setIsCartOpen(false),
    addToWishlist,
    removeFromWishlist,
    toggleWishlist,
    isWishlisted: (productId: number) =>
      wishlistItems.some((item) => item.id === productId),
    isWishlistedByName: (productName: string) =>
      wishlistItems.some((item) => item.name === productName),
    addToCart,
    removeFromCart,
    increaseQty,
    decreaseQty,
    clearCart,
    moveWishlistItemToCart,
  };

  return <CommerceContext.Provider value={value}>{children}</CommerceContext.Provider>;
};

export const useCommerce = (): CommerceContextValue => {
  const context = useContext(CommerceContext);
  if (!context) {
    throw new Error("useCommerce must be used within a CommerceProvider");
  }
  return context;
};
