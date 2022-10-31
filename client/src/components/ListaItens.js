import React, {Children, useContext, useEffect, useLayoutEffect, useRef, useState} from "react";
import {Link, useNavigate} from "react-router-dom";

import useAxiosPrivate from "../hooks/useAxiosPrivate";
import useQuery from "../hooks/useQuery";
import useInvalidSessionHandler from "../hooks/useInvalidSessionHandler";

import {FontAwesomeIcon} from "@fortawesome/react-fontawesome";
import {faSort, faSortDown, faSortUp} from "@fortawesome/free-solid-svg-icons";

import "../assets/css/lista-itens.css";
import Paginador from "./Paginador";
import ListaContext from "../context/ProvedorLista";

const tiposHeader = ["limitado", "ilimitado", "data", "icone"];

export default function ListaItens(props) {
    const axios = useAxiosPrivate();
    const query = useQuery();
    const navigate = useNavigate();
    const handleInvalidSession = useInvalidSessionHandler();

    const {pagina, setPagina, ordenar, setOrdenar, ordem, setOrdem, urls, setUrls, pesquisa, setPesquisa} = useContext(ListaContext);
    const permitirDisplayPesquisa = props.permitirPesquisa ? {display: "block"} : {display: "none"};

    const pesquisaRef = useRef();
    const navegacaoSuperiorRef = useRef();
    const [resultado, setResultado] = useState({});

    const ITENS_POR_PAGINA = 50;
    const QTD_COLUNAS = Children.count(props.tableHeaders.props.children);
    const QUERY_STRING = `?pesquisa=${pesquisa || '""'}&ordenar=${ordenar}&ordem=${ordem}`;

    const handlePesquisar = (event) => {
        event.preventDefault();

        let novaPesquisa = event.target.pesquisa.value === "" ? '""' : event.target.pesquisa.value;
        const navegarPara = `${urls.pagina}?pesquisa=${novaPesquisa}&ordenar=${ordenar}&ordem=${ordem}&pagina=1`;

        setPagina(1);
        setPesquisa(novaPesquisa);
        pesquisaRef.current.value = novaPesquisa !== '""' ? novaPesquisa : "";

        navigate(navegarPara);
    }

    const handleNavegacao = (_pagina) => {
        return () => {
            setPagina(_pagina);
        }
    }

    useLayoutEffect(() => {
        setOrdenar(props.defaultOrdenar);
        setOrdem(props.defaultOrdem);
        setUrls(props.urls);
    }, []);

    useEffect(() => {
        let isMounted = true;
        const controlador = new AbortController();

        let paginaAtual = parseInt(query.get("pagina")) || 1;
        let ordenarPor = query.get("ordenar") || ordenar;
        let ordemPor = query.get("ordem") || ordem;
        let pesquisaPor = query.get("pesquisa") || '""';
        setPagina(paginaAtual);
        setOrdenar(ordenarPor);
        setOrdem(ordemPor);
        setUrls(props.urls);
        setPesquisa(pesquisaPor);
        pesquisaRef.current.value = pesquisaPor !== '""' ? pesquisaPor : "";

        const url = `${props.urls.backend}?pesquisa=${pesquisaPor}&ordenar=${ordenarPor}&ordem=${ordemPor}&limite=${ITENS_POR_PAGINA}&pagina=${paginaAtual}`;

        const obterVisitantes = async () => {
            try {
                const resposta = await axios.get(url, {signal: controlador.signal});
                isMounted && setResultado(resposta.data);
            } catch (e) {
                if (e.response?.status === 401) {
                    handleInvalidSession();
                } else {
                    if (e.code !== "ERR_CANCELED") {
                        console.log(e);
                    }
                }
            }
        }

        obterVisitantes();

        return () => {
            isMounted = false;
            controlador.abort();
        }
    },[pagina, ordem, ordenar, pesquisa, props.urls]);

    return (
        <>
            <div className="conteudo width--95">
                <form onSubmit={handlePesquisar} style={permitirDisplayPesquisa}>
                    <div className="campo-pesquisa">
                        <label className="campo-pesquisa__elemento" htmlFor="pesquisa">Pesquisar</label>
                        <input className="form-control campo-pesquisa__elemento" type="text" name="pesquisa" id="pesquisa"
                               placeholder={props.placeholder} ref={pesquisaRef}/>
                        <button type="submit" className="btn btn-primary campo-pesquisa__elemento">
                            Pesquisar
                        </button>
                    </div>
                </form>

                <hr style={permitirDisplayPesquisa}/>

                <div className="navegacao" ref={navegacaoSuperiorRef}>
                    <Paginador
                        urlPagina={urls.pagina}
                        queryString={QUERY_STRING}
                        paginaAtual={resultado.paginaAtual}
                        quantidadePaginas={resultado.quantidadePaginas}
                        handleNavegacao={handleNavegacao}
                    />

                    {
                        urls.adicionarItem ? (
                            <Link to={`${urls.adicionarItem}`}>
                                <button type="button" className="btn btn-dark">Adicionar</button>
                            </Link>
                        ) : ""
                    }
                </div>

                <table className="table table-hover table-striped">
                    <thead>
                    <tr className="table-dark">
                        {props.tableHeaders}
                    </tr>
                    </thead>
                    <tbody>
                    {resultado.quantidadeTotal > 0
                        ? resultado.dados.map(props.mapFunction)
                        : <tr><td className="sem-resultado" colSpan={QTD_COLUNAS}>Nenhum item encontrado</td></tr>}
                    </tbody>
                </table>

                <Paginador
                    foco={navegacaoSuperiorRef}
                    urlPagina={urls.pagina}
                    queryString={QUERY_STRING}
                    paginaAtual={resultado.paginaAtual}
                    quantidadePaginas={resultado.quantidadePaginas}
                    handleNavegacao={handleNavegacao}
                />
            </div>
        </>
    )
}

export function TableHeader(props) {
    const navigate = useNavigate();
    const {setPagina, ordenar, setOrdenar, ordem, setOrdem, urls, pesquisa} = useContext(ListaContext);

    function getOrdemIcone(campo) {
        if (campo === ordenar) {
            return ordem === "ASC" ? faSortUp : faSortDown;
        }
        return faSort;
    }

    function handleOrdenar(event) {
        let ordemPor = "ASC";
        if (ordenar === event.currentTarget.id.toLowerCase()) {
            ordemPor = ordem === "ASC" ? "DESC" : "ASC";
        }

        let ordenarPor = event.currentTarget.id.toLowerCase();
        let navegarPara = `${urls.pagina}?pesquisa=${pesquisa}&ordenar=${ordenarPor}&ordem=${ordemPor}&pagina=1`;

        setOrdem(ordemPor);
        setOrdenar(ordenarPor);
        setPagina(1);

        navigate(navegarPara);
    }

    if (!tiposHeader.includes(props.tipo.toLowerCase())) {
        return;
    }

    const titulo = props.titulo ?? "";
    const icone = props.icone ? <FontAwesomeIcon icon={props.icone}/> : "";

    return props.tipo !== "icone"
        ? (
            <th className={props.tipo}>
                <div className="th__conteudo" onClick={handleOrdenar} id={props.id}>
                    <FontAwesomeIcon icon={getOrdemIcone(props.id)} className="th__conteudo__elemento"/>
                    {titulo}
                </div>
            </th>
        )
        : (
            <th id={props.id} className={`${props.tipo} icone__th`}>
                {titulo}
                {icone}
            </th>
        )
}

export function TableData(props) {
    if (!tiposHeader.includes(props.tipo.toLowerCase())) {
        return;
    }

    return (
        <td className={props.tipo}>
            {props.children}
        </td>
    )
}
