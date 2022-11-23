import React, {useEffect, useState} from "react";
import {Link} from "react-router-dom";


import {faBuilding, faCirclePlus, faPenToSquare, faUser} from "@fortawesome/free-solid-svg-icons";
import {FontAwesomeIcon} from "@fortawesome/react-fontawesome";

import {mascaraCPF} from "../assets/js/dados-visitante";

import Titulo from "../components/Titulo";
import ListaItens, {TableData, TableHeader} from "../components/ListaItens";
import {ProvedorLista} from "../context/ProvedorLista";
import useQuery from "../hooks/useQuery";

export default function ListaVisitantes() {
    const query = useQuery();
    const [parametro, setParametro] = useState({
        dataInicio: query.get("dataInicio") ?? "",
        dataFim: query.get("dataFim") ?? "",
        status: query.get("status") ?? ""
    });

    useEffect(() => {
        let dataInicio = query.get("dataInicio") ?? "";
        let dataFim = query.get("dataFim") ?? "";
        let status = query.get("status") ?? "";

        setParametro({dataInicio, dataFim, status});
    }, [query]);

    const tableHeaders = (
        <>
            <TableHeader id="cpf" tipo="limitado" titulo="CPF"/>
            <TableHeader id="nome" tipo="ilimitado" titulo="Nome"/>
            <TableHeader id="data_nascimento" tipo="data" titulo="Data de nascimento"/>
            <TableHeader id="cadastrado_em" tipo="data" titulo="Cadastrado em"/>
            <TableHeader id="modificado_em" tipo="data" titulo="Modificado em"/>
            <TableHeader id="nova_visita" tipo="icone" icone={faBuilding} tooltip="Adicionar visitante"/>
            <TableHeader id="detalhes" tipo="icone" icone={faUser} tooltip="Detalhes do visitante"/>
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

                <TableData tipo="icone" tooltip="Adicionar visitante">
                    <Link to={`/nova-visita?cpf=${visitante.cpf}`}>
                        <FontAwesomeIcon icon={faCirclePlus}/>
                    </Link>
                </TableData>
                <TableData tipo="icone" tooltip="Detalhes do visitante">
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
                    labelAdicionar="Cadastrar novo visitante"
                    parametro={parametro}
                    defaultOrdenar="cadastrado_em"
                    defaultOrdem="DESC"
                    tableHeaders={tableHeaders}
                    mapFunction={mapFunction}
                    placeholder="Insira um cpf, nome ou data de cadastro"
                    permitirPesquisa
                    paginacao
                    tableHover
                    tableStriped
                    tableDark
                />
            </ProvedorLista>
        </>
    )
}
