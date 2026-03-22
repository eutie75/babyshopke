import { Star } from "lucide-react";
import giftRegistry from "@/assets/gift-registry.png";
import happyParents from "@/assets/happy-parents.png";

interface BottomCardsProps {
  onGetStarted: () => void;
}

const BottomCards = ({ onGetStarted }: BottomCardsProps) => {
  return (
    <section className="max-w-[1400px] mx-auto px-4 md:px-8 pb-16">
      <div className="grid md:grid-cols-2 gap-6">
        {/* Gift Registry */}
        <div className="bg-baby-mint rounded-2xl p-6 md:p-8 flex items-center gap-6 shadow-soft overflow-hidden">
          <img
            src={giftRegistry}
            alt="Gift registry bunny"
            className="w-28 md:w-36 shrink-0 drop-shadow-lg"
          />
          <div className="space-y-3">
            <h3 className="text-2xl font-extrabold text-foreground">Gift Registry</h3>
            <p className="text-sm text-muted-foreground">
              Create or find a baby gift registry easily.
            </p>
            <button
              type="button"
              onClick={onGetStarted}
              className="px-6 py-2.5 rounded-full bg-primary text-primary-foreground font-bold text-sm hover:brightness-105 hover:shadow-glow-primary transition-all duration-200"
            >
              Get Started
            </button>
          </div>
        </div>

        {/* Happy Parents */}
        <div className="bg-baby-peach rounded-2xl p-6 md:p-8 flex items-center gap-6 shadow-soft overflow-hidden">
          <img
            src={happyParents}
            alt="Happy parents giraffe"
            className="w-28 md:w-36 shrink-0 drop-shadow-lg"
          />
          <div className="space-y-3">
            <h3 className="text-2xl font-extrabold text-foreground">Happy Parents</h3>
            <p className="text-sm text-muted-foreground">
              Join thousands of satisfied families!
            </p>
            <div className="flex items-center gap-2">
              <div className="flex items-center gap-0.5">
                {Array.from({ length: 5 }).map((_, i) => (
                  <Star
                    key={i}
                    className={`w-4 h-4 ${
                      i < 4 ? "text-amber-400 fill-amber-400" : "text-amber-400 fill-amber-400/50"
                    }`}
                  />
                ))}
              </div>
              <span className="text-sm font-bold text-foreground">4.3</span>
              <span className="text-xs text-muted-foreground">(2.0k Reviews)</span>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default BottomCards;
