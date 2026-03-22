import { FormEvent, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { Lock, LogIn, Mail, ShieldCheck } from "lucide-react";
import { toast } from "sonner";
import UserPageLayout from "@/components/UserPageLayout";
import BrandLogo from "@/components/BrandLogo";
import { setStoredAuthUser } from "@/lib/auth";

const Login = () => {
  const navigate = useNavigate();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");

  const deriveNameFromEmail = (userEmail: string): string => {
    const localPart = userEmail.split("@")[0] ?? "Parent";
    const withSpaces = localPart.replace(/[._-]+/g, " ").trim();

    if (!withSpaces) {
      return "Parent";
    }

    return withSpaces
      .split(" ")
      .filter(Boolean)
      .map((chunk) => chunk.charAt(0).toUpperCase() + chunk.slice(1))
      .join(" ");
  };

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    if (!email.trim() || !password.trim()) {
      toast.error("Enter both email and password.");
      return;
    }

    const normalizedEmail = email.trim().toLowerCase();
    setStoredAuthUser({
      fullName: deriveNameFromEmail(normalizedEmail),
      email: normalizedEmail,
    });

    toast.success("Login successful.");
    navigate("/account");
  };

  return (
    <UserPageLayout title="Login" description="Access your Baby Shop KE account.">
      <section className="relative mx-auto max-w-4xl">
        <div className="pointer-events-none absolute -left-12 top-10 h-48 w-48 rounded-full bg-primary/25 blur-3xl" />
        <div className="pointer-events-none absolute -right-14 -top-8 h-52 w-52 rounded-full bg-accent/25 blur-3xl" />

        <div className="relative rounded-[30px] bg-gradient-to-br from-primary/35 via-white/60 to-accent/35 p-[1px] shadow-card">
          <div className="glassmorphism grid overflow-hidden rounded-[29px] border-white/40 md:grid-cols-[0.9fr_1.1fr]">
            <aside className="border-b border-white/50 bg-white/45 px-6 py-8 md:border-b-0 md:border-r md:px-8 md:py-10">
              <div className="inline-flex items-center gap-3 rounded-2xl border border-white/70 bg-white/75 px-3 py-2 shadow-soft">
                <BrandLogo imageClassName="h-12 w-12" />
                <div>
                  <p className="text-sm font-semibold text-muted-foreground">Welcome back to</p>
                  <h2 className="text-xl font-extrabold text-foreground">Baby Shop KE</h2>
                </div>
              </div>

              <p className="mt-6 text-sm leading-6 text-muted-foreground">
                Sign in to manage your family account, track orders, and unlock age-based product recommendations.
              </p>

              <div className="mt-6 space-y-2.5 text-sm">
                <div className="inline-flex items-center gap-2 rounded-full border border-white/70 bg-white/75 px-3 py-1.5 font-semibold text-foreground">
                  <ShieldCheck className="h-4 w-4 text-primary" />
                  Secure sign-in
                </div>
              </div>
            </aside>

            <div className="px-6 py-8 md:px-8 md:py-10">
              <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                  <label className="mb-2 block text-sm font-semibold">Email</label>
                  <div className="flex items-center gap-2 rounded-2xl border border-white/70 bg-white/75 px-4 focus-within:border-primary/50 focus-within:ring-2 focus-within:ring-primary/20">
                    <Mail className="h-4 w-4 text-muted-foreground" />
                    <input
                      type="email"
                      value={email}
                      onChange={(event) => setEmail(event.target.value)}
                      className="w-full bg-transparent py-3.5 text-sm outline-none placeholder:text-muted-foreground"
                      placeholder="you@example.com"
                      required
                    />
                  </div>
                </div>

                <div>
                  <label className="mb-2 block text-sm font-semibold">Password</label>
                  <div className="flex items-center gap-2 rounded-2xl border border-white/70 bg-white/75 px-4 focus-within:border-primary/50 focus-within:ring-2 focus-within:ring-primary/20">
                    <Lock className="h-4 w-4 text-muted-foreground" />
                    <input
                      type="password"
                      value={password}
                      onChange={(event) => setPassword(event.target.value)}
                      className="w-full bg-transparent py-3.5 text-sm outline-none placeholder:text-muted-foreground"
                      placeholder="Enter password"
                      required
                    />
                  </div>
                </div>

                <button
                  type="submit"
                  className="inline-flex w-full items-center justify-center gap-2 rounded-full bg-primary py-3 font-bold text-primary-foreground shadow-glow-primary transition hover:brightness-105"
                >
                  <LogIn className="h-4 w-4" />
                  Login
                </button>
              </form>

              <div className="mt-5 flex flex-wrap gap-4 text-sm text-muted-foreground">
                <Link to="/register" className="font-semibold text-primary hover:opacity-80">
                  Create account
                </Link>
                <Link to="/get-started" className="font-semibold text-primary hover:opacity-80">
                  Back to get started
                </Link>
              </div>

              <div className="mt-4">
                <Link
                  to="/shop"
                  className="text-xs font-semibold uppercase tracking-wide text-muted-foreground hover:text-foreground"
                >
                  Continue as guest
                </Link>
              </div>
            </div>
          </div>
        </div>
      </section>
    </UserPageLayout>
  );
};

export default Login;
