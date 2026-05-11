import { Route } from "react-router-dom";
import { PublicLayout } from "../../layouts/PublicLayout";
import { LoginPage } from "../../features/auth/pages/LoginPage";
import { RegistroPage } from "../../features/auth/pages/RegistroPage";
import { HomePage } from "../../features/public/pages/HomePage";

export const publicRoutes = (
    <Route element={<PublicLayout />}>
        <Route index element={<HomePage />} />
        <Route path="login" element={<LoginPage />} />
        <Route path="registro" element={<RegistroPage />} />
    </Route>
);
