import React from "react";
import {
    Route,
    createBrowserRouter,
    createRoutesFromElements,
    RouterProvider,
} from "react-router-dom";
import Dailysales from "../pages/dailysales";
import Vendorsales from "../pages/vendorsales";

const router = createBrowserRouter(
    createRoutesFromElements(
        <Route>
            <Route path="/dailysales" element={<Dailysales />} />
            <Route path="/vendorsales" element={<Vendorsales />} />
        </Route>
    )
);

const App = () => {
    return (
        <>
            <RouterProvider router={router} />
        </>
    );
};

export default App;
