import { Link } from "react-router-dom";
import type { RelatedResource } from "../api/types";

export function RelatedLinks({
  items,
  basePath,
}: {
  items: RelatedResource[];
  basePath?: string;
}) {
  return (
    <span className="text-md">
      {items.map((item, i) => (
        <span key={item.id}>
          {basePath ? (
            <Link
              to={`${basePath}/${item.id}`}
              className="text-blue-400 hover:text-blue-800"
            >
              {item.name}
            </Link>
          ) : (
            <span>{item.name}</span>
          )}
          {i < items.length - 1 && ", "}
        </span>
      ))}
    </span>
  );
}
