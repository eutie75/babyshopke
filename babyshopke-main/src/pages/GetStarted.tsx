import { Link } from "react-router-dom";
import { ArrowRight, LogIn, ShieldCheck, ShoppingBag, UserPlus } from "lucide-react";
import UserPageLayout from "@/components/UserPageLayout";
import BrandLogo from "@/components/BrandLogo";

const GetStarted = () => {
  return (
    <UserPageLayout
      title="Get Started"
      description="Create your account or login to start shopping and managing your family profile."
    >
      <section className="relative mx-auto max-w-3xl">
        <div className="pointer-events-none absolute -left-12 -top-10 h-40 w-40 rounded-full bg-primary/25 blur-3xl" />
        <div className="pointer-events-none absolute -bottom-10 -right-12 h-44 w-44 rounded-full bg-accent/25 blur-3xl" />

        <div className="relative rounded-[30px] bg-gradient-to-br from-primary/35 via-white/55 to-accent/35 p-[1px] shadow-card">
          <div className="glassmorphism rounded-[29px] border-white/40 px-6 py-8 md:px-10 md:py-10">
            <div className="mx-auto mb-6 flex w-fit items-center justify-center rounded-2xl border border-white/70 bg-white/80 p-3 shadow-soft">
              <BrandLogo imageClassName="h-16 w-16" />
            </div>

            <div className="text-center">
              <h2 className="text-2xl md:text-3xl font-extrabold text-foreground">
                Welcome to Baby Shop KE
              </h2>
              <p className="mt-2 text-muted-foreground">
                Curated essentials for babies and growing kids, delivered across Kenya.
              </p>
            </div>

            <div className="mt-6 grid gap-3 md:grid-cols-3">
              <Link
                to="/register"
                className="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-3 font-bold text-primary-foreground shadow-glow-primary transition hover:brightness-105"
              >
                <UserPlus className="h-4 w-4" />
                Create Account
              </Link>

              <Link
                to="/login"
                className="inline-flex items-center justify-center gap-2 rounded-full border border-white/70 bg-white/65 px-6 py-3 font-bold text-foreground transition hover:bg-white/80"
              >
                <LogIn className="h-4 w-4" />
                Login
              </Link>

              <Link
                to="/shop"
                className="inline-flex items-center justify-center gap-2 rounded-full bg-accent px-6 py-3 font-bold text-accent-foreground shadow-glow-accent transition hover:brightness-105"
              >
                <ShoppingBag className="h-4 w-4" />
                Continue Shopping
              </Link>
            </div>

            <div className="mt-6 flex flex-wrap items-center justify-center gap-3 text-xs font-semibold text-muted-foreground">
              <span className="inline-flex items-center gap-1 rounded-full border border-white/70 bg-white/70 px-3 py-1.5">
                <ShieldCheck className="h-3.5 w-3.5 text-primary" />
                Secure checkout
              </span>
              <span className="inline-flex items-center gap-1 rounded-full border border-white/70 bg-white/70 px-3 py-1.5">
                Same-day dispatch in Nairobi
              </span>
              <span className="inline-flex items-center gap-1 rounded-full border border-white/70 bg-white/70 px-3 py-1.5">
                Family account ready
              </span>
            </div>

            <div className="mt-5 text-center">
              <Link
                to="/shop"
                className="inline-flex items-center gap-1 text-sm font-bold text-primary transition hover:opacity-80"
              >
                Browse latest arrivals <ArrowRight className="h-4 w-4" />
              </Link>
            </div>
          </div>
        </div>
      </section>
    </UserPageLayout>
  );
};

export default GetStarted;
