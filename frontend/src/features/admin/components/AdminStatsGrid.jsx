import { AdminStatCard } from "./AdminStatCard";

export function AdminStatsGrid({ stats = [] }) {
    return (
        <section className="admin-metrics-grid admin-stats-grid">
            {stats.map((stat) => <AdminStatCard key={stat.title} {...stat} />)}
        </section>
    );
}
