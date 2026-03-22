import { Link } from "react-router-dom";
import UserPageLayout from "@/components/UserPageLayout";

const orders = [
  {
    id: 1042,
    date: "2026-02-24",
    total: 4900,
    status: "pending",
    paymentMethod: "MPESA_SIM",
    deliveryOption: "delivery",
  },
  {
    id: 1038,
    date: "2026-02-18",
    total: 2650,
    status: "shipped",
    paymentMethod: "COD",
    deliveryOption: "pickup",
  },
];

const statusClass: Record<string, string> = {
  pending: "bg-amber-100 text-amber-700",
  paid: "bg-emerald-100 text-emerald-700",
  shipped: "bg-sky-100 text-sky-700",
  delivered: "bg-green-100 text-green-700",
};

const Orders = () => {
  return (
    <UserPageLayout title="My Orders" description="Track all your orders and delivery status.">
      <div className="bg-card rounded-2xl border border-border p-4 md:p-6 shadow-soft overflow-x-auto">
        <table className="w-full min-w-[700px]">
          <thead>
            <tr className="text-left text-xs uppercase tracking-wide text-muted-foreground border-b border-border">
              <th className="py-3">Order #</th>
              <th className="py-3">Date</th>
              <th className="py-3">Total</th>
              <th className="py-3">Payment</th>
              <th className="py-3">Delivery</th>
              <th className="py-3">Status</th>
            </tr>
          </thead>
          <tbody>
            {orders.map((order) => (
              <tr key={order.id} className="border-b border-border last:border-0">
                <td className="py-3 font-bold">#{order.id}</td>
                <td className="py-3 text-sm">{order.date}</td>
                <td className="py-3 font-semibold">KSH {order.total.toLocaleString()}</td>
                <td className="py-3 text-sm">{order.paymentMethod}</td>
                <td className="py-3 text-sm">{order.deliveryOption}</td>
                <td className="py-3">
                  <span className={`px-2.5 py-1 rounded-full text-xs font-bold ${statusClass[order.status]}`}>
                    {order.status}
                  </span>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      <div className="mt-4">
        <Link to="/shop" className="px-5 py-2.5 rounded-full border border-border bg-card font-semibold">
          Continue Shopping
        </Link>
      </div>
    </UserPageLayout>
  );
};

export default Orders;
