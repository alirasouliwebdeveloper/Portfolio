import { cn } from "@/lib/cn";

type CodeCardProps = {
  name: string;
  stack: string[];
  passion: string;
  className?: string;
};

/** The floating "developer object" card from the hero. Purely decorative (aria-hidden). */
export function CodeCard({ name, stack, passion, className }: CodeCardProps) {
  const string = (value: string) => (
    <span className="text-code-string">&quot;{value}&quot;</span>
  );

  return (
    <div
      aria-hidden="true"
      dir="ltr"
      className={cn(
        "rounded-tag-sm border-border-input bg-surface-input shadow-float tablet:text-xs desktop:text-[0.78rem] border font-mono text-[0.72rem] leading-[1.75]",
        className,
      )}
    >
      <div className="border-divider text-text-2 flex items-center justify-between border-b px-3.5 py-2.5 font-sans text-xs">
        <span>&lt;/&gt; Code</span>
        <span className="bg-online size-2 rounded-full" />
      </div>
      <pre className="text-code m-0 px-3.5 pt-3 pb-3.5 whitespace-pre-wrap">
        <span className="text-icon-soft">const</span> developer = {"{"}
        {"\n  name: "}
        {string(name)},{"\n  stack: ["}
        {stack.map((item, index) => (
          <span key={item}>
            {string(item)}
            {index < stack.length - 1 ? ", " : ""}
          </span>
        ))}
        ],{"\n  passion: "}
        {string(passion)}
        {"\n};"}
      </pre>
    </div>
  );
}
