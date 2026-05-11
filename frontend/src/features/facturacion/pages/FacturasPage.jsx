import { Link } from "react-router-dom";
import { AppIcon } from "../../../components/ui/AppIcon";
import { appIcons } from "../../../config/appIcons";

export function FacturasPage() {
    return (
        <section className="page">
            <header className="page-header">
                <h1>Facturas</h1>
                <p>Listado preparado para facturasService.list.</p>
            </header>
            <div className="actions">
                <Link className="table-action" to="/app/facturas/1">
                    <AppIcon icon={appIcons.ver} size={16} />
                    Ver factura de ejemplo
                </Link>
                <Link className="table-action" to="/app/facturas/registros">
                    <AppIcon icon={appIcons.facturas} size={16} />
                    Registros de facturacion
                </Link>
                <Link className="table-action" to="/app/facturas/verifactu">
                    <AppIcon icon={appIcons.validar} size={16} />
                    VeriFactu
                </Link>
            </div>
        </section>
    );
}
