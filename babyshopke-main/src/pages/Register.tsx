import { FormEvent, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import {
  Lock,
  Mail,
  ShieldCheck,
  Sparkles,
  User,
  UserCheck,
  UserPlus,
} from "lucide-react";
import { toast } from "sonner";
import UserPageLayout from "@/components/UserPageLayout";
import BrandLogo from "@/components/BrandLogo";
import { setStoredAuthUser } from "@/lib/auth";

const Register = () => {
  const navigate = useNavigate();
  const [fullName, setFullName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    if (!fullName.trim() || !email.trim() || !password.trim() || !confirmPassword.trim()) {
      toast.error("Fill in all fields.");
      return;
    }

    if (password.length < 8) {
      toast.error("Password must be at least 8 characters.");
      return;
    }

    if (password !== confirmPassword) {
      toast.error("Passwords do not match.");
      return;
    }

    setStoredAuthUser({
      fullName: fullName.trim(),
      email: email.trim().toLowerCase(),
    });

    toast.success("Account created successfully.");
    navigate("/account");
  };

  return (
    <UserPageLayout title="Create Account" description="Join Baby Shop KE in a few steps.">
      <section className="relative mx-auto max-w-4xl">
        <div className="pointer-events-none absolute -left-14 -top-12 h-56 w-56 rounded-full bg-primary/25 blur-3xl" />
        <div className="pointer-events-none absolute -bottom-14 -right-10 h-52 w-52 rounded-full bg-accent/25 blur-3xl" />

        <div className="relative rounded-[30px] bg-gradient-to-br from-primary/35 via-white/60 to-accent/35 p-[1px] shadow-card">
          <div className="glassmorphism grid overflow-hidden rounded-[29px] border-white/40 md:grid-cols-[0.95fr_1.05fr]">
            <aside className="border-b border-white/50 bg-white/45 px-6 py-8 md:border-b-0 md:border-r md:px-8 md:py-10">
              <div className="inline-flex items-center gap-3 rounded-2xl border border-white/70 bg-white/75 px-3 py-2 shadow-soft">
                <BrandLogo imageClassName="h-12 w-12" />
                <div>
                  <p className="text-sm font-semibold text-muted-foreground">Start your journey with</p>
                  <h2 className="text-xl font-extrabold text-foreground">Baby Shop KE</h2>
                </div>
              </div>

              <p className="mt-6 text-sm leading-6 text-muted-foreground">
                Create your family account to save favorites, manage child profiles, and get personalized baby picks.
              </p>

              <div className="mt-6 space-y-2.5 text-sm">
                <div className="inline-flex items-center gap-2 rounded-full border border-white/70 bg-white/75 px-3 py-1.5 font-semibold text-foreground">
                  <ShieldCheck className="h-4 w-4 text-primary" />
                  Secure registration
                </div>
                <div className="inline-flex items-center gap-2 rounded-full border border-white/70 bg-white/75 px-3 py-1.5 font-semibold text-foreground">
                  <Sparkles className="h-4 w-4 text-accent" />
                  Family-first shopping tools
                </div>
              </div>
            </aside>

            <div className="px-6 py-8 md:px-8 md:py-10">
              <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                  <label className="mb-2 block text-sm font-semibold">Full Name</label>
                  <div className="flex items-center gap-2 rounded-2xl border border-white/70 bg-white/75 px-4 focus-within:border-primary/50 focus-within:ring-2 focus-within:ring-primary/20">
                    <User className="h-4 w-4 text-muted-foreground" />
                    <input
                      type="text"
                      value={fullName}
                      onChange={(event) => setFullName(event.target.value)}
                      className="w-full bg-transparent py-3.5 text-sm outline-none placeholder:text-muted-foreground"
                      placeholder="Your full name"
                      required
                    />
                  </div>
                </div>

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
                      placeholder="Minimum 8 characters"
                      required
                    />
                  </div>
                </div>

                <div>
                  <label className="mb-2 block text-sm font-semibold">Confirm Password</label>
                  <div className="flex items-center gap-2 rounded-2xl border border-white/70 bg-white/75 px-4 focus-within:border-primary/50 focus-within:ring-2 focus-within:ring-primary/20">
                    <UserCheck className="h-4 w-4 text-muted-foreground" />
                    <input
                      type="password"
                      value={confirmPassword}
                      onChange={(event) => setConfirmPassword(event.target.value)}
                      className="w-full bg-transparent py-3.5 text-sm outline-none placeholder:text-muted-foreground"
                      placeholder="Re-enter password"
                      required
                    />
                  </div>
                </div>

                <button
                  type="submit"
                  className="inline-flex w-full items-center justify-center gap-2 rounded-full bg-primary py-3 font-bold text-primary-foreground shadow-glow-primary transition hover:brightness-105"
                >
                  <UserPlus className="h-4 w-4" />
                  Create Account
                </button>
              </form>

              <div className="mt-5 flex flex-wrap gap-4 text-sm text-muted-foreground">
                <Link to="/login" className="font-semibold text-primary hover:opacity-80">
                  Already have an account?
                </Link>
                <Link to="/get-started" className="font-semibold text-primary hover:opacity-80">
                  Back to get started
                </Link>
              </div>
            </div>
          </div>
        </div>
      </section>
    </UserPageLayout>
  );
};

export default Register;
