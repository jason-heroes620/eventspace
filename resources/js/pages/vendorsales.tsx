import React, { useEffect, useState } from "react";
import axiosConfig from "../utils/axiosConfig";

type Event = {
    id: number;
    event_name: string;
};

const Vendorsales = () => {
    const [events, setEvents] = useState<Event>();
    const [selectedEvent, setSelectedEvent] = useState("");
    const [salesReport, setSalesReport] = useState([]);
    const [total, setTotal] = useState("0.00");

    useEffect(() => {
        axiosConfig.get("/events").then((resp) => {
            setEvents(resp.data.data);
        });
    }, []);

    const handleEventChange = (event: any) => {
        // setSelectedEvent(event.target.value);
        axiosConfig.get(`/vendorsales/${event.target.value}`).then((resp) => {
            const data = resp.data.data;
            setSalesReport(data);
        });
    };
    return (
        <div className="container">
            <div className="py-4 col col-md-6 col-lg-3">
                <select
                    name=""
                    id=""
                    onChange={handleEventChange}
                    className="form-select"
                >
                    <option value="">Select An Event</option>
                    {events?.map((event: Event) => {
                        return (
                            <option key={event.id} value={event.id}>
                                {event.event_name}
                            </option>
                        );
                    })}
                </select>
            </div>
            <div>
                {salesReport.length > 0 ? (
                    <div>
                        <table className="table table-condensed table-hover table-sm">
                            <thead className="table-info">
                                <tr>
                                    <th>Vendor</th>
                                    <th className="text-end">
                                        Total Sales (RM)
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="small">
                                {salesReport.map((sales, i) => {
                                    return (
                                        <tr key={i}>
                                            <td>{sales.organization}</td>
                                            <td className="text-end">
                                                {sales.total}
                                            </td>
                                        </tr>
                                    );
                                })}
                                <tr>
                                    <td className="text-end">
                                        <strong>Total</strong>
                                    </td>
                                    <td className="text-end">
                                        <strong>
                                            {salesReport
                                                .reduce(
                                                    (a, v) =>
                                                        (a += parseFloat(
                                                            v.total
                                                        )),
                                                    0
                                                )
                                                .toFixed(2)}
                                        </strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                ) : (
                    ""
                )}
            </div>
        </div>
    );
};

export default Vendorsales;
