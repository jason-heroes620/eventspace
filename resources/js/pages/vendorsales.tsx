import React, { useEffect, useState } from "react";
import axiosConfig from "../utils/axiosConfig";
import { saveAs } from "file-saver";
import * as XLSX from "xlsx";

type Event = {
    id: number;
    event_name: string;
};

const fileType =
    "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=UTF-8";
const fileExtension = ".xlsx";

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
        setSelectedEvent(event.target.value);
        axiosConfig.get(`/vendorsales/${event.target.value}`).then((resp) => {
            const data = resp.data.data;
            setSalesReport(data);
        });
    };

    const handleExport = async (vendor) => {
        const resp = axiosConfig.get(
            `/vendorsalesbyvendorid/${selectedEvent}/vendor/${vendor}`
        );
        const res = (await resp).data.data;
        const worksheet = XLSX.utils.json_to_sheet(res);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Sheet1");
        const excelBuffer = XLSX.write(workbook, {
            bookType: "xlsx",
            type: "array",
        });
        const blob = new Blob([excelBuffer], {
            type: "application/octet-stream",
        });
        saveAs(blob, `${vendor}.xlsx`);
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
                        <table className="table table-hover">
                            <thead className="table-info">
                                <tr>
                                    <th>Vendor</th>
                                    <th className="text-end">
                                        Total Sales (RM)
                                    </th>
                                    <th></th>
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
                                            <td className="d-flex align-items-center">
                                                <button
                                                    className="btn btn-info"
                                                    onClick={() =>
                                                        handleExport(sales.id)
                                                    }
                                                >
                                                    Export To Excel
                                                </button>
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
