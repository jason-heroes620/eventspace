import React from "react";

const SalesDetails = ({ sales }) => {
    return (
        <table className="table table-hover">
            <thead className="table-info">
                <tr>
                    <th>Order Number</th>
                    <th>Payment Ref.</th>
                    <th>Payment Method</th>
                    <th>Issuing Bank</th>
                    <th className="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                {sales.map((s, i) => {
                    return (
                        <tr key={i}>
                            <td>{s.id}</td>
                            <td>{s.payment_ref}</td>
                            <td>{s.payment_method}</td>
                            <td>{s.issuing_bank}</td>
                            <td className="text-end">{s.total}</td>
                        </tr>
                    );
                })}
                <tr>
                    <td colSpan={4} className="text-end">
                        <strong>Total</strong>
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
