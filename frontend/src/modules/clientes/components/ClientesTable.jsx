import { Link } from "react-router-dom";

export function ClientesTable({ clientes = [] }) {
    if (!clientes.length) {
        return <p className="empty-state">No hay clientes cargados todavia.</p>;
    }

    return (
        <table className="data-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Telefono</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                {clientes.map((cliente) => (
                    <tr key={cliente.id}>
                        <td>{cliente.nombre}</td>
                        <td>{cliente.email}</td>
                        <td>{cliente.telefono}</td>
                        <td>
                            <Link to={`/app/clientes/${cliente.id}`}>Ver</Link>
                        </td>
                    </tr>
                ))}
            </tbody>
        </table>
    );
}
