import React, { useEffect, useState } from "react";
import axiosConfig from "../utils/axiosConfig";
import moment from "moment";
import SalesSummary from "../components/salesSummary";
import SalesDetails from "../components/salesDetails";

type Event = {
    id: number;
    event_name: string;
    event_start_date: Date;
    event_end_date: Date;
};
const Dailysales = () => {
    const [events, setEvents] = useState<Event[]>([]);
    const [selectedEvent, setSelectedEvent] = useState("");
    const [eventDates, setEventDates] = useState([]);
    const [selectedDate, setSelectedDate] = useState("");
    const [sales, setSales] = useState([]);
    const [reportType, setReportType] = useState("summary");

    useEffect(() => {
        axiosConfig.get("/events").then((resp) => {
            setEvents(resp.data.data);
        });
    }, []);

    const handleEventChange = (event: any) => {
        setSelectedEvent(event.target.value);

        const dates = events.filter((e) => e.id == event.target.value);
        let date = new Date(dates[0].event_start_date);
        let newDates = [];
        newDates.push(moment(date).format("yyyy-MM-DD"));
        while (date < new Date(dates[0].event_end_date)) {
            let newDate = date.setDate(date.getDate() + 1);
            newDates.push(moment(newDate).format("yyyy-MM-DD"));
            date = new Date(newDate);
        }
        setEventDates(newDates);
    };

    const handleDateChange = (event: any) => {
        setSelectedDate(event.target.value);
        axiosConfig
            .get(
                `/sales/${selectedEvent}/date/${event.target.value}/type/${reportType}`
            )
            .then((resp) => {
                setSales(resp.data.data);
            });
    };

    const handleReportTypeChange = (report) => {
        setReportType(report);
        axiosConfig
            .get(`/sales/${selectedEvent}/date/${selectedDate}/type/${report}`)
            .then((resp) => {
                console.log(resp.data.data);
                setSales(resp.data.data);
            });
    };

    return (
        <div className="container">
            <div className="py-4 col-12 col-md-6 col-lg-3">
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
            <div className="col-12 col-md-6 col-lg-3">
                <select
                    name=""
                    id=""
                    onChange={handleDateChange}
                    className="form-select"
                >
                    <option value="">Select a date</option>
                    {eventDates.map((d, i) => {
                        return (
                            <option value={d} key={i}>
                                {moment(d).format("DD/MM/y")}
                            </option>
                        );
                    })}
                </select>
            </div>
            <div className="d-flex py-2 flex-col">
                {sales.length > 0 ? (
                    <div>
                        <div className="py-4 d-flex flex-row">
                            <div className="px-2">
                                <button
                                    onClick={() =>
                                        handleReportTypeChange("summary")
                                    }
                                    className={
                                        reportType === "summary"
                                            ? "btn btn-info"
                                            : "btn border"
                                    }
                                >
                                    Summary
                                </button>
                            </div>
                            <div className="px-2">
                                <button
                                    onClick={() =>
                                        handleReportTypeChange("detail")
                                    }
                                    className={
                                        reportType === "detail"
                                            ? "btn btn-info"
                                            : "btn border"
                                    }
                                >
                                    Detail
                                </button>
                            </div>
                        </div>
                        {reportType === "summary" ? (
                            <SalesSummary sales={sales} />
                        ) : (
                            <SalesDetails sales={sales} />
                        )}
                    </div>
                ) : (
                    <div className="d-flex flex-col align-items-center">
                        <h4>
                            <strong>
                                No sales for the selected event & date
                            </strong>
                        </h4>
                    </div>
                )}
            </div>
        </div>
    );
};

export default Dailysales;
