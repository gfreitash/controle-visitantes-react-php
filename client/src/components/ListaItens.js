import React, {Children, useContext, useEffect, useRef, useState} from "react";
import {Link, useNavigate} from "react-router-dom";

import useAxiosPrivate from "../hooks/useAxiosPrivate";
import useQuery from "../hooks/useQuery";
import useInvalidSessionHandler from "../hooks/useInvalidSessionHandler";

import {FontAwesomeIcon} from "@fortawesome/react-fontawesome";
import {faSort, faSortDown, faSortUp, faSquareCaretDown, faSquareCaretUp} from "@fortawesome/free-solid-svg-icons";

import "../assets/css/lista-itens.css";
import "bootstrap/dist/js/bootstrap.bundle.min"
import Paginador from "./Paginador";
import ListaContext from "../context/ProvedorLista";

const tiposHeader = ["limitado", "ilimitado", "data", "icone"];

function paramsParaString(paramObj) {
    let queryString = "";
    for (const propriedade in paramObj) {
        if (paramObj[propriedade].length > 0) {
            queryString.length > 0 && (queryString += "&");
            queryString += `${propriedade}=${paramObj[propriedade]}`
        }
    }

    return queryString;
}

function obterQuery(parametro, pesquisa, ordenar, ordem, pagina, limite) {
    let query = "";
    const concatQuery = (propriedade, valor) => {
        query.length > 0 && (query += "&");
        query += `${propriedade}=${valor}`;
    }

    parametro?.length > 0 && (query += parametro);
    pesquisa?.length > 0 && concatQuery("pesquisa", pesquisa);
    ordenar?.length > 0 && concatQuery("ordenar", ordenar);
    ordem?.length > 0 && concatQuery("ordem", ordem);
    limite > 0 && concatQuery("limite", limite);
    pagina > 0 && concatQuery("pagina", pagina);

    return `?${query}`;
}

export default function ListaItens(props) {
    const axios = useAxiosPrivate();
    const query = useQuery();
    const navigate = useNavigate();
    const handleInvalidSession = useInvalidSessionHandler();

    const {setPagina, ordenar, setOrdenar, ordem, setOrdem, urls, setUrls, pesquisa, setPesquisa} = useContext(ListaContext);
    const permitirDisplayPesquisa = props.permitirPesquisa ? {display: "block"} : {display: "none"};

    const [resultado, setResultado] = useState({});
    const [parametro, setParametro] = useState("");
    const [showAvancado, setShowAvancado] = useState(false);
    const parametroVazio = useRef(false);
    const pesquisaRef = useRef();
    const navegacaoSuperiorRef = useRef();

    const ITENS_POR_PAGINA = 50;
    const QTD_COLUNAS = Children.count(props.tableHeaders.props.children);
    const QUERY_STRING = obterQuery(parametro, pesquisa, ordenar, ordem);
    const MOSTRAR_PARAMS = (parametro.length > 0 && paramsParaString(props.parametro) === parametro);

    const obterParametro = () => {
        if (parametro.length > 0) {
            parametroVazio.current = false;
            return parametro;
        } else if (parametroVazio.current) {
            return "";
        } else {
            parametroVazio.current = false;
            setParametro(props.parametro);
            return paramsParaString(props.parametro);
        }
    }

    const handlePesquisar = (event) => {
        event.preventDefault();

        const novaPesquisa = event.target.pesquisa.value;
        const query = obterQuery(parametro, novaPesquisa, ordenar, ordem, 1);
        const navegarPara = `${urls.pagina}${query}`;

        setPagina(1);
        setPesquisa(novaPesquisa);
        pesquisaRef.current.value = novaPesquisa;

        navigate(navegarPara);
    }

    const handleParametroChange = (event) => {
        parametroVazio.current = event.target.value === "";
        setShowAvancado(true);
        setParametro(event.target.value);
    }

    const handleNavegacao = (_pagina) => {
        return () => {
            setPagina(_pagina);
        }
    }

    useEffect(() => {
        let isMounted = true;
        const controlador = new AbortController();

        let paginaAtual = parseInt(query.get("pagina")) || 1;
        let ordenarPor = query.get("ordenar") || ordenar || props.defaultOrdenar;
        let ordemPor = query.get("ordem") || ordem || props.defaultOrdem;
        let pesquisaPor = query.get("pesquisa") ?? "";
        let param = obterParametro();

        setPagina(paginaAtual);
        setOrdenar(ordenarPor);
        setOrdem(ordemPor);
        setUrls(props.urls);
        setPesquisa(pesquisaPor);
        setParametro(param);
        pesquisaRef.current.value = pesquisaPor;

        const params = obterQuery(param, pesquisaPor, ordenarPor, ordemPor, paginaAtual, ITENS_POR_PAGINA);
        const url = `${props.urls.backend}${params}`;

        const obterVisitantes = async () => {
            try {
                const resposta = await axios.get(url, {signal: controlador.signal});
                setResultado(resposta.data);
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

        isMounted && obterVisitantes();

        return () => {
            isMounted = false;
            controlador.abort();
        }
    },[query, props]);

    return (
        <>
            <div className="conteudo width--95">
                <form onSubmit={handlePesquisar}>
                    <div className="campo-pesquisa">
                        <label className="campo-pesquisa__elemento" htmlFor="pesquisa" style={permitirDisplayPesquisa}>
                            Pesquisar
                        </label>
                        <input className="form-control campo-pesquisa__elemento" type="text" name="pesquisa" id="pesquisa"
                               placeholder={props.placeholder} ref={pesquisaRef} style={permitirDisplayPesquisa}/>

                        <button type="button" className="btn btn-dark campo-pesquisa__elemento" data-bs-toggle="collapse"
                                data-bs-target="#parametros-container" aria-expanded="true" aria-controls="parametros-container"
                                onClick={()=>setShowAvancado(!showAvancado)}
                        >
                            <div className="d-flex align-items-center">
                                Avançado
                                <FontAwesomeIcon icon={showAvancado ? faSquareCaretUp : faSquareCaretDown} className="ms-2"/>
                            </div>
                        </button>
                        <button type="submit" className="btn btn-primary campo-pesquisa__elemento">
                            Pesquisar
                        </button>
                    </div>

                    <div className="accordion">
                        <div id="parametros-container" aria-labelledby="headingOne"
                             className={`accordion-collapse collapse mt-2 ${MOSTRAR_PARAMS? "show"  : ""}`}>
                                <div className="campo-pesquisa campo-pesquisa__avancado p-1">
                                    <label className="campo-pesquisa__elemento">Parametros: </label>
                                    <input id="parametro" name="parametro" className="form-control campo-pesquisa__elemento"
                                           value={parametro} placeholder="param1=valor1&param2=valor2..."
                                           type="text" onChange={handleParametroChange}/>
                                </div>
                        </div>
                    </div>
                </form>

                <hr/>

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
