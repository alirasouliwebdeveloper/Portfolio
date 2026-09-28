import type { ComponentProps, ComponentType } from "react";
import {
  ArrowRight as LucideArrowRight,
  ArrowUpRight as LucideArrowUpRight,
  Bell,
  BookOpen,
  Calendar,
  ChevronDown as LucideChevronDown,
  ChevronLeft as LucideChevronLeft,
  ChevronRight as LucideChevronRight,
  Check,
  CircleAlert,
  Clock,
  Code,
  CreditCard,
  Database,
  Download,
  File,
  Heart,
  House,
  LayoutGrid,
  Lightbulb,
  Mail,
  Menu,
  MessageSquare,
  Minus,
  Monitor,
  Phone,
  MapPin,
  ClipboardList,
  Plus,
  Rocket,
  Search,
  Send,
  Server,
  Shield,
  Smile,
  ShoppingCart,
  TrendingUp,
  Trophy,
  Upload,
  User,
  Network,
  X,
  type LucideProps,
} from "lucide-react";
import { cn } from "@/lib/cn";

/**
 * Fixed icon set. The API and Filament store these names; the frontend maps
 * them to SVG components here (docs/02-architecture.md, "Icons").
 */
export const iconMap = {
  alert: CircleAlert,
  bell: Bell,
  book: BookOpen,
  bulb: Lightbulb,
  calendar: Calendar,
  card: CreditCard,
  cart: ShoppingCart,
  chat: MessageSquare,
  check: Check,
  clock: Clock,
  code: Code,
  db: Database,
  download: Download,
  file: File,
  flow: Network,
  grid: LayoutGrid,
  heart: Heart,
  home: House,
  mail: Mail,
  menu: Menu,
  minus: Minus,
  phone: Phone,
  pin: MapPin,
  plan: ClipboardList,
  plus: Plus,
  rocket: Rocket,
  screen: Monitor,
  search: Search,
  send: Send,
  server: Server,
  shield: Shield,
  smile: Smile,
  trend: TrendingUp,
  trophy: Trophy,
  upload: Upload,
  user: User,
  x: X,
} as const satisfies Record<string, ComponentType<LucideProps>>;

export type IconName = keyof typeof iconMap;

export const iconNames = Object.keys(iconMap) as IconName[];

type IconProps = Omit<LucideProps, "ref"> & { name: IconName };

export function Icon({ name, strokeWidth = 1.8, ...props }: IconProps) {
  const Component = iconMap[name];
  return (
    <Component
      aria-hidden={props["aria-label"] ? undefined : true}
      strokeWidth={strokeWidth}
      {...props}
    />
  );
}

type DirectionalProps = Omit<ComponentProps<typeof LucideArrowRight>, "ref">;

function flip(Component: ComponentType<LucideProps>, displayName: string) {
  function Directional({ className, ...props }: DirectionalProps) {
    return (
      <Component
        aria-hidden="true"
        strokeWidth={1.8}
        className={cn("rtl:-scale-x-100", className)}
        {...props}
      />
    );
  }
  Directional.displayName = displayName;
  return Directional;
}

export const ArrowRight = flip(LucideArrowRight, "ArrowRight");
export const ArrowUpRight = flip(LucideArrowUpRight, "ArrowUpRight");
export const ChevronLeft = flip(LucideChevronLeft, "ChevronLeft");
export const ChevronRight = flip(LucideChevronRight, "ChevronRight");

export function ChevronDown({ className, ...props }: DirectionalProps) {
  return (
    <LucideChevronDown
      aria-hidden="true"
      strokeWidth={1.8}
      className={className}
      {...props}
    />
  );
}
