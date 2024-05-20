import React from "react";

const SalesSummary = ({ sales }) => {
    return (
        <table className="table table-hover">
            <thead className="table-info">
                <tr>
                    <th>Product</th>
                    <th>Vendor</th>
                    <th className="text-end">Quantity</th>
                    <th className="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                {sales.map((s, i) => {
                    return (
                        <tr key={i}>
                            <td>{s.product_name}</td>
                            <td>{s.organization}</td>
                            <td className="text-end">{s.quantity}</td>
                            <td className="text-end">{s.sales}</td>
                        </tr>
                    );
                })}
                <tr>
                    <td colSpan={3} className="text-end">
                        <strong>
                            {sales.reduce(
                                (a, v) => (a += parseFloat(v.quantity)),
                                0
                            )}
                        </strong>
                    </td>
                    <td className="text-end">
                        <strong>
                            {sales
                                .reduce((a, v) => (a += parseFloat(v.sales)), 0)
                                .toFixed(2)}
                        </strong>
                    </td>
                </tr>
            </tbody>
        </table>
    );
};

export default SalesSummary;
