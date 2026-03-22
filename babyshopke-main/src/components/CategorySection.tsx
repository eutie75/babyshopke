import { Baby, Milk, Puzzle, Shirt } from "lucide-react";

const categories = [
  { name: "Diapers & Wipes", icon: Baby, color: "bg-baby-mint" },
  { name: "Feeding", icon: Milk, color: "bg-baby-peach" },
  { name: "Toys", icon: Puzzle, color: "bg-baby-mint" },
  { name: "Clothing", icon: Shirt, color: "bg-baby-peach" },
];

interface CategorySectionProps {
  activeCategory: string | null;
  onCategorySelect: (category: string) => void;
}

const CategorySection = ({ activeCategory, onCategorySelect }: CategorySectionProps) => {
  return (
    <section id="categories" className="max-w-[1400px] mx-auto px-4 md:px-8 -mt-4 relative z-10">
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        {categories.map((cat) => (
          <button
            key={cat.name}
            type="button"
            onClick={() => onCategorySelect(cat.name)}
            className={`${cat.color} rounded-2xl p-5 flex items-center gap-3 shadow-soft hover:shadow-card hover:scale-[1.02] transition-all duration-200 group cursor-pointer ${
              activeCategory === cat.name ? "ring-2 ring-primary ring-offset-2 ring-offset-background" : ""
            }`}
          >
            <div className="w-12 h-12 rounded-xl bg-card flex items-center justify-center shadow-soft group-hover:shadow-glow-primary transition-shadow">
              <cat.icon className="w-6 h-6 text-primary" />
            </div>
            <span className="font-bold text-foreground text-sm md:text-base">{cat.name}</span>
          </button>
        ))}
      </div>
    </section>
  );
};

export default CategorySection;
