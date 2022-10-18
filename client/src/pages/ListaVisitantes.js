import React from "react";
import {Link} from "react-router-dom";


import {faBuilding, faCirclePlus, faPenToSquare, faUser} from "@fortawesome/free-solid-svg-icons";
import {FontAwesomeIcon} from "@fortawesome/react-fontawesome";

import {mascaraCPF} from "../assets/js/modules/dados-visitante";

import Titulo from "../components/Titulo";
import ListaItens, {TableData, TableHeader} from "../components/ListaItens";
import {ProvedorLista} from "../context/ProvedorLista";

export default function ListaVisitantes() {
    const tableHeaders = (
        <>
            <TableHeader id="cpf" tipo="limitado" titulo="CPF"/>
            <TableHeader id="nome" tipo="ilimitado" titulo="Nome"/>
            <TableHeader id="data_nascimento" tipo="data" titulo="Data de nascimento"/>
            <TableHeader id="cadastrado_em" tipo="data" titulo="Cadastrado em"/>
            <TableHeader id="modificado_em" tipo="data" titulo="Modificado em"/>
            <TableHeader id="nova_visita" tipo="icone" icone={faBuilding}/>
            <TableHeader id="detalhes" tipo="icone" icone={faUser}/>
        </>
    );

    const mapFunction = (visitante) => {
        const data_nascimento = visitante.data_nascimento
            ? new Date(Date.parse(visitante.data_nascimento))
            : null;
        const cadastrado_em = visitante.cadastrado_em
            ? new Date(Date.parse(visitante.cadastrado_em))
            : null;
        const modificado_em = visitante.modificado_em
            ? new Date(Date.parse(visitante.modificado_em))
            : null;

        return (
            <tr key={visitante.id}>
                <TableData tipo="limitado">{mascaraCPF(visitante.cpf)}</TableData>
                <TableData tipo="ilimitado">{visitante.nome}</TableData>
                <TableData tipo="data">{data_nascimento?.toLocaleDateString()}</TableData>
                <TableData tipo="data">{cadastrado_em?.toLocaleString()}</TableData>
                <TableData tipo="data">{modificado_em?.toLocaleString()}</TableData>

                <TableData tipo="icone">
                    <Link to={`/nova-visita?cpf=${visitante.cpf}`}>
                        <FontAwesomeIcon icon={faCirclePlus}/>
                    </Link>
                </TableData>
                <TableData tipo="icone">
                    <Link to={`/visitante?cpf=${visitante.cpf}`}>
                        <FontAwesomeIcon icon={faPenToSquare}/>
                    </Link>
                </TableData>
            </tr>
        )
    }

    return (
        <>
            <Titulo>Lista de Visitantes</Titulo>
            <hr/>
            <ProvedorLista>
                <ListaItens
                    urls={{pagina: "/lista-visitantes", backend: "/visitante", adicionarItem: "/novo-cadastro"}}
                    defaultOrdenar="cadastrado_em"
                    defaultOrdem="DESC"
                    placeholder="Insira um cpf, nome ou data de cadastro"
                    tableHeaders={tableHeaders}
                    mapFunction={mapFunction}
                    permitirPesquisa
                />
            </ProvedorLista>
        </>
    )
}
