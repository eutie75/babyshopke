import heroBaby from "@/assets/hero-baby.png";

interface HeroSectionProps {
  onShopNow: () => void;
}

const HeroSection = ({ onShopNow }: HeroSectionProps) => {
  return (
    <section className="relative overflow-hidden">
      {/* Background gradient */}
      <div className="absolute inset-0 bg-gradient-to-br from-baby-mint via-background to-baby-peach opacity-60" />
      
      <div className="relative max-w-[1400px] mx-auto px-4 md:px-8 py-12 md:py-20">
        <div className="grid md:grid-cols-2 gap-8 items-center">
          {/* Left Content */}
          <div className="space-y-6 animate-fade-in-up">
            <h1 className="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight text-foreground">
              Everything{" "}
              <span className="text-primary">Your Baby</span>
              <br />
              Needs in One Place
            </h1>
            <p className="text-lg text-muted-foreground max-w-md">
              A curated selection of top-quality baby products.
            </p>

            {/* CTA Button */}
            <button
              type="button"
              onClick={onShopNow}
              className="inline-flex items-center px-8 py-3.5 rounded-full bg-accent text-accent-foreground font-bold text-lg shadow-glow-accent hover:brightness-105 hover:scale-[1.02] transition-all duration-200"
            >
              Shop Now
            </button>

            {/* Payment Options */}
            <div className="glassmorphism inline-flex p-3 rounded-2xl">
              <img
                src="/paymentoptions.png"
                alt="Payment options"
                className="h-11 md:h-14 w-auto object-contain"
              />
            </div>
          </div>

          {/* Right Image */}
          <div className="flex justify-center md:justify-end">
            <img
              src={heroBaby}
              alt="Happy baby with toys and shopping cart"
              className="w-full max-w-lg animate-float drop-shadow-2xl"
            />
          </div>
        </div>
      </div>
    </section>
  );
};

export default HeroSection;
