import { useEffect, useState } from "react";
import { Toaster } from "@/components/ui/toaster";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { TooltipProvider } from "@/components/ui/tooltip";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter, Navigate, Routes, Route } from "react-router-dom";
import AppLoader from "./components/AppLoader";
import CommerceDrawers from "./components/CommerceDrawers";
import { CommerceProvider } from "./context/CommerceContext";
import Index from "./pages/Index";
import NotFound from "./pages/NotFound";
import GetStarted from "./pages/GetStarted";
import Login from "./pages/Login";
import Register from "./pages/Register";
import Account from "./pages/Account";
import Checkout from "./pages/Checkout";
import Orders from "./pages/Orders";
import Shop from "./pages/Shop";
import AdminDashboard from "./pages/AdminDashboard";

const queryClient = new QueryClient();

const App = () => {
  const [showBootLoader, setShowBootLoader] = useState(true);

  useEffect(() => {
    const timerId = window.setTimeout(() => {
      setShowBootLoader(false);
    }, 2000);

    return () => window.clearTimeout(timerId);
  }, []);

  if (showBootLoader) {
    return <AppLoader />;
  }

  return (
    <CommerceProvider>
      <QueryClientProvider client={queryClient}>
        <TooltipProvider>
          <Toaster />
          <Sonner />
          <BrowserRouter>
            <CommerceDrawers />
            <Routes>
              <Route path="/" element={<Index />} />
              <Route path="/get-started" element={<GetStarted />} />
              <Route path="/login" element={<Login />} />
              <Route path="/register" element={<Register />} />
              <Route path="/account" element={<Account />} />
              <Route path="/wishlist" element={<Navigate to="/shop" replace />} />
              <Route path="/cart" element={<Navigate to="/shop" replace />} />
              <Route path="/checkout" element={<Checkout />} />
              <Route path="/orders" element={<Orders />} />
              <Route path="/shop" element={<Shop />} />
              <Route path="/admin/*" element={<Navigate to="/admin/dashboard" replace />} />
              <Route path="/admin/dashboard" element={<AdminDashboard />} />
              <Route path="*" element={<NotFound />} />
            </Routes>
          </BrowserRouter>
        </TooltipProvider>
      </QueryClientProvider>
    </CommerceProvider>
  );
};

export default App;
