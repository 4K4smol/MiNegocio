import { BrowserRouter, Navigate, Route, Routes } from "react-router-dom";
import { adminRoutes } from "./adminRoutes";
import { SplashPage } from "../../features/auth/pages/SplashPage";
import { privateRoutes } from "./privateRoutes";
import { PrivateRoute } from "./PrivateRoute";
import { publicRoutes } from "./publicRoutes";

export function AppRouter() {
    return (
        <BrowserRouter>
            <Routes>
                {publicRoutes}
                <Route element={<PrivateRoute />}>
                    <Route path="splash" element={<SplashPage />} />
                </Route>
                {privateRoutes}
                {adminRoutes}
                <Route path="*" element={<Navigate to="/" replace />} />
            </Routes>
        </BrowserRouter>
    );
}
