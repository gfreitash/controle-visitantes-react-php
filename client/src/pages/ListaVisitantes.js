import React, {useEffect, useRef, useState} from "react";
import {Link, useNavigate} from "react-router-dom";


import {faSort, faSortUp, faSortDown,faBuilding, faUser, faCirclePlus, faPen} from "@fortawesome/free-solid-svg-icons";
import {FontAwesomeIcon} from "@fortawesome/react-fontawesome";

import "../assets/css/lista-visitantes.css";
import useAxiosPrivate from "../hooks/useAxiosPrivate";
import useQuery from "../hooks/useQuery";
import {mascaraCPF} from "../assets/js/modules/dados-visitante";

import Titulo from "../components/Titulo";
import Paginador from "../components/Paginador";

export default function ListaVisitantes() {
    const axios = useAxiosPrivate();
    const query = useQuery();
    const navigate = useNavigate();

    const [resultado, setResultado] = useState({});
    const [pesquisa, setPesquisa] = useState('""');
    const [pagina, setPagina] = useState(1);
    const [ordenar, setOrdenar] = useState("cadastrado_em");
    const [ordem, setOrdem] = useState("DESC");

    const pesquisaRef = useRef();

    const ITENS_POR_PAGINA = 50;
    const URL_BACKEND = "/visitante";
    const URL_PAGINA = "/lista-visitantes";
    const ORDENAR = ["cpf", "nome", "data_nascimento", "cadastrado_em", "modificado_em"];
    const QUERY_STRING = `?pesquisa=${pesquisa || '""'}&ordenar=${ordenar}&ordem=${ordem}`;

    function getIcone(campo) {
        if (campo === ordenar) {
            return ordem === "ASC" ? faSortUp : faSortDown;
        }
        return faSort;
    }

    async function handleOrdenar(event) {
        if(!ORDENAR.includes(event.target.id.toLowerCase())) {
            return;
        }

        let ordemPor = ordem === "ASC" ? "DESC" : "ASC";
        let ordenarPor = event.target.id.toLowerCase();
        let navegarPara = `${URL_PAGINA}?pesquisa=${pesquisa}&ordenar=${ordenarPor}&ordem=${ordemPor}&pagina=1`;

        setOrdem(ordemPor);
        setOrdenar(ordenarPor);
        setPagina(1);

        navigate(navegarPara);
    }

    const handleNavegacao = (pagina) => {
        return () => {setPagina(pagina)}
    }

    const handleSubmit = (event) => {
        event.preventDefault();

        let novaPesquisa = event.target.pesquisa.value === "" ? '""' : event.target.pesquisa.value;
        let navegarPara = `${URL_PAGINA}?pesquisa=${novaPesquisa}&ordenar=${ordenar}&ordem=${ordem}&pagina=1`;
        console.log(novaPesquisa);
        setPagina(1);
        setPesquisa(novaPesquisa);
        pesquisaRef.current.value = novaPesquisa !== '""' ? novaPesquisa : "";

        navigate(navegarPara);
    }


    useEffect(() => {
        let isMounted = true;
        const controlador = new AbortController();

        let paginaAtual = parseInt(query.get("pagina")) || 1;
        let ordenarPor = query.get("ordenar") || "cadastrado_em";
        let ordemPor = query.get("ordem") || "DESC";
        let pesquisaPor = query.get("pesquisa") || '""';
        setPagina(paginaAtual);
        setOrdenar(ordenarPor);
        setOrdem(ordemPor);
        setPesquisa(pesquisaPor);
        pesquisaRef.current.value = pesquisaPor !== '""' ? pesquisaPor : "";

        const url = `${URL_BACKEND}?pesquisa=${pesquisa}&ordenar=${ordenar}&ordem=${ordem}&limite=${ITENS_POR_PAGINA}&pagina=${pagina}`;

        const obterVisitantes = async () => {
            try {
                const resposta = await axios.get(url, {signal: controlador.signal});
                isMounted && setResultado(resposta.data);
            } catch (e) {
                console.log(e);
            }
        }

        obterVisitantes();

        return () => {
            isMounted = false;
            controlador.abort();
        }
    },[pagina, ordenar, ordem, pesquisa]);

    return (
        <>
            <Titulo>Lista de Visitantes</Titulo>
            <hr/>
            <div className="conteudo width--95">
                <form onSubmit={handleSubmit}>
                    <div className="campo-pesquisa">
                        <label className="campo-pesquisa__elemento" htmlFor="pesquisa">Pesquisar</label>
                        <input ref={pesquisaRef} className="form-control campo-pesquisa__elemento" type="text" id="pesquisa" name="pesquisa"
                               placeholder="Insira um cpf, nome ou data de cadastro"/>
                        <button type="submit" className="btn btn-primary campo-pesquisa__elemento">
                            Pesquisar
                        </button>
                    </div>
                </form>

                <hr/>

                <div className="navegacao">
                    <Paginador
                        urlPagina={URL_PAGINA}
                        queryString={QUERY_STRING}
                        paginaAtual={resultado.paginaAtual}
                        quantidadePaginas={resultado.quantidadePaginas}
                        handleNavegacao={handleNavegacao}
                    />

                    <Link to="/novo-cadastro">
                        <button type="button" className="btn btn-dark">Incluir novo visitante</button>
                    </Link>
                </div>

                <table className="table table-hover table-striped">
                    <thead>
                    <tr className="table-dark">
                        <th onClick={handleOrdenar} id="cpf">
                            <FontAwesomeIcon icon={getIcone("cpf")}/>
                            CPF
                        </th>
                        <th onClick={handleOrdenar} id="nome">
                            <FontAwesomeIcon icon={getIcone("nome")}/>
                            Nome
                        </th>
                        <th onClick={handleOrdenar} id="data_nascimento">
                            <FontAwesomeIcon icon={getIcone("data_nascimento")}/>
                            Data de nascimento
                        </th>
                        <th onClick={handleOrdenar} id="cadastrado_em">
                            <FontAwesomeIcon icon={getIcone("cadastrado_em")}/>
                            Data do cadastro
                        </th>
                        <th onClick={handleOrdenar} id="modificado_em">
                            <FontAwesomeIcon icon={getIcone("modificado_em")}/>
                            Data de modificação

                        </th>
                        <th className="icone-th">
                            <FontAwesomeIcon icon={faBuilding}/>
                        </th>
                        <th className="icone-th">
                            <FontAwesomeIcon icon={faUser}/>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    {resultado.quantidadeVisitantes > 0 ?
                        resultado.visitantes?.map(
                            (visitante) => {
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
                                        <td>{mascaraCPF(visitante.cpf)}</td>
                                        <td>{visitante.nome}</td>
                                        <td>{data_nascimento?.toLocaleString()}</td>
                                        <td>{cadastrado_em?.toLocaleString()}</td>
                                        <td>{modificado_em?.toLocaleString()}</td>

                                        <td>
                                            <Link to={`/nova-visita?cpf=${visitante.cpf}`}>
                                                <FontAwesomeIcon icon={faCirclePlus}/>
                                            </Link>
                                        </td>
                                        <td>
                                            <Link to={`/visitante?cpf=${visitante.cpf}`}>
                                                <FontAwesomeIcon icon={faPen}/>
                                            </Link>
                                        </td>
                                    </tr>
                                )
                            }
                        ) : (<tr><td colSpan={7} className="sem-resultado"><p>Sem resultados</p></td></tr>)
                    }
                    </tbody>
                </table>
                <Paginador
                    urlPagina={URL_PAGINA}
                    queryString={QUERY_STRING}
                    paginaAtual={resultado.paginaAtual}
                    quantidadePaginas={resultado.quantidadePaginas}
                    handleNavegacao={handleNavegacao}
                />
            </div>
        </>
    )
}
