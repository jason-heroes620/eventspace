import React from "react";

const SalesDetails = ({ sales }) => {
    return (
        <table className="table table-hover">
            <thead className="table-info">
                <tr>
                    <th>Product</th>
                    <th>Vendor</th>
                    <th>Payment Ref.</th>
                    <th>Payment Method</th>
                    <th>Issuing Bank</th>
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
                            <td>{s.payment_ref}</td>
                            <td>{s.payment_method}</td>
                            <td>{s.issuing_bank}</td>
                            <td className="text-end">{s.quantity}</td>
                            <td className="text-end">{s.total}</td>
                        </tr>
                    );
                })}
                <tr>
                    <td colSpan={6} className="text-end">
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
                                .reduce((a, v) => (a += parseFloat(v.total)), 0)
                                .toFixed(2)}
                        </strong>
                    </td>
                </tr>
            </tbody>
        </table>
    );
};

export default SalesDetails;
