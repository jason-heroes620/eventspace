import React, { useEffect, useState } from "react";
import axiosConfig from "../utils/axiosConfig";

type Event = {
    id: number;
    event_name: string;
};
const Dailysales = () => {
    const [events, setEvents] = useState<Event>();
    const [selectedEvent, setSelectedEvent] = useState("");

    useEffect(() => {
        axiosConfig.get("/events").then((resp) => {
            setEvents(resp.data.data);
        });
    }, []);

    const handleEventChange = (event: any) => {
        setSelectedEvent(event.target.value);
    };
    return (
        <div className="container">
            <div className="py-4">
                <select
                    name=""
                    id=""
                    onChange={handleEventChange}
                    className="form-select"
                >
                    <option value="">Select An Event</option>
                    {events?.map((event: Event) => {
                        return (
                            <option key={event.id} value="event.id">
                                {event.event_name}
                            </option>
                        );
                    })}
                </select>
            </div>
        </div>
    );
};

export default Dailysales;
