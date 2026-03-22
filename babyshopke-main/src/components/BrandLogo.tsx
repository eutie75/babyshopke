import { Link } from "react-router-dom";

interface BrandLogoProps {
  className?: string;
  imageClassName?: string;
}

const BrandLogo = ({ className = "", imageClassName = "" }: BrandLogoProps) => {
  return (
    <Link to="/" className={`inline-flex items-center ${className}`}>
      <img
        src="/logo.png"
        alt="Baby Shop KE logo"
        className={`w-10 h-10 object-contain ${imageClassName}`}
      />
      <span className="sr-only">Baby Shop KE</span>
    </Link>
  );
};

export default BrandLogo;

