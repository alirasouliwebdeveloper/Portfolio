import { Card } from "@/components/ui/Card";
import { IconTile } from "@/components/ui/IconTile";
import { Tag } from "@/components/ui/Tag";
import type { AboutContent } from "@/types/api";

export function ToolboxGroups({
  groups,
}: {
  groups: AboutContent["toolbox"]["groups"];
}) {
  return (
    <div className="tablet:grid-cols-2 tablet:gap-6 desktop:grid-cols-4 grid gap-5">
      {groups.map((group) => (
        <Card key={group.title} hover className="flex flex-col gap-4">
          <div className="flex items-center gap-3.5">
            <IconTile name={group.icon} size="sm" />
            <h3 className="text-card-title text-text font-semibold">
              {group.title}
            </h3>
          </div>
          <ul className="flex flex-wrap gap-2">
            {group.tags.map((tag) => (
              <li key={tag}>
                <Tag>{tag}</Tag>
              </li>
            ))}
          </ul>
        </Card>
      ))}
    </div>
  );
}
