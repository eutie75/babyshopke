import { Link } from "react-router-dom";
import UserPageLayout from "@/components/UserPageLayout";

const stats = [
  { label: "Total Orders", value: "328", helper: "12 new today" },
  { label: "Total Products", value: "96", helper: "4 added this week" },
  { label: "Low Stock Items", value: "9", helper: "Needs restock" },
  { label: "Revenue (KSH)", value: "1,284,500", helper: "This month" },
];

const recentOrders = [
  { id: 1259, customer: "Grace Njeri", total: 5200, status: "pending" },
  { id: 1258, customer: "Brian Kiptoo", total: 3900, status: "paid" },
  { id: 1257, customer: "Faith Wambui", total: 6150, status: "shipped" },
  { id: 1256, customer: "Mercy Atieno", total: 2800, status: "delivered" },
];

const lowStock = [
  { product: "Ultra Dry Diapers Pack", category: "Diapers & Wipes", stock: 3 },
  { product: "Silicone Feeding Set", category: "Feeding", stock: 4 },
  { product: "Interactive Stacking Cups", category: "Toys", stock: 2 },
  { product: "Cotton Sleep Suit", category: "Clothing", stock: 5 },
];

const statusClass: Record<string, string> = {
  pending: "bg-amber-100 text-amber-700",
  paid: "bg-emerald-100 text-emerald-700",
  shipped: "bg-sky-100 text-sky-700",
  delivered: "bg-green-100 text-green-700",
};

const AdminDashboard = () => {
  return (
    <UserPageLayout
      title="Admin Dashboard"
      description="Monitor orders, inventory, and sales performance in one place."
    >
      <section className="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {stats.map((stat) => (
          <article key={stat.label} className="bg-card rounded-2xl border border-border p-4 shadow-soft">
            <p className="text-sm text-muted-foreground">{stat.label}</p>
            <p className="text-3xl font-extrabold text-foreground mt-1">{stat.value}</p>
            <p className="text-xs text-muted-foreground mt-1">{stat.helper}</p>
          </article>
        ))}
      </section>

      <section className="grid lg:grid-cols-2 gap-4">
        <article className="bg-card rounded-2xl border border-border p-4 shadow-soft">
          <div className="flex items-center justify-between mb-3">
            <h2 className="text-lg font-extrabold">Recent Orders</h2>
            <Link to="/orders" className="text-sm font-semibold text-primary">
              View all
            </Link>
          </div>
          <div className="space-y-2">
            {recentOrders.map((order) => (
              <div key={order.id} className="border border-border rounded-xl p-3 flex items-center justify-between gap-3">
                <div>
                  <p className="font-bold">#{order.id}</p>
                  <p className="text-sm text-muted-foreground">{order.customer}</p>
                </div>
                <div className="text-right">
                  <p className="font-semibold">KSH {order.total.toLocaleString()}</p>
                  <span className={`px-2.5 py-1 rounded-full text-xs font-bold ${statusClass[order.status]}`}>
                    {order.status}
                  </span>
                </div>
              </div>
            ))}
          </div>
        </article>

        <article className="bg-card rounded-2xl border border-border p-4 shadow-soft">
          <div className="flex items-center justify-between mb-3">
            <h2 className="text-lg font-extrabold">Low Stock Alerts</h2>
            <Link to="/shop" className="text-sm font-semibold text-primary">
              Open shop
            </Link>
          </div>
          <div className="space-y-2">
            {lowStock.map((item) => (
              <div key={item.product} className="border border-border rounded-xl p-3">
                <p className="font-bold">{item.product}</p>
                <p className="text-sm text-muted-foreground">
                  {item.category} • Stock left: {item.stock}
                </p>
              </div>
            ))}
          </div>
        </article>
      </section>
    </UserPageLayout>
  );
};

export default AdminDashboard;
