import { BrowserRouter, Navigate, Route, Routes } from "react-router-dom";
import { adminRoutes } from "./adminRoutes";
import { privateRoutes } from "./privateRoutes";
import { publicRoutes } from "./publicRoutes";

export function AppRouter() {
    return (
        <BrowserRouter>
            <Routes>
                {publicRoutes}
                {privateRoutes}
                {adminRoutes}
                <Route path="*" element={<Navigate to="/" replace />} />
            </Routes>
        </BrowserRouter>
    );
}
