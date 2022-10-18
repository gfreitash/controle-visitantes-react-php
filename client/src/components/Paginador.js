import React from "react";
import {Pagination} from "react-bootstrap";
import {LinkContainer} from "react-router-bootstrap";

import "../assets/css/paginador.css";

export default function Paginador(props) {
    const URL_PAGINA = props.urlPagina;
    const QUERY_STRING = props.queryString;
    const handleNavegacao = (pagina) => {
        return () => {
            props.handleNavegacao(pagina)();
            if (props.foco) {
               props.foco.current.scrollIntoView({inline: "start", behavior: "smooth"});
            }
        }
    }

    const paginacao = (quantidadePaginas, paginaAtual) => {
        quantidadePaginas = parseInt(quantidadePaginas);
        paginaAtual = parseInt(paginaAtual);
        if (!quantidadePaginas || !paginaAtual) return "";

        let primeira = 1;
        let ultima = quantidadePaginas;
        let anterior = paginaAtual - 1;
        let proxima = paginaAtual + 1;
        let antAnterior = paginaAtual - 2;
        let proxProxima = paginaAtual + 2;

        let linkUltima = paginaAtual === ultima ? "#" : `${URL_PAGINA}${QUERY_STRING}&pagina=${ultima}`;

        return (
            <>
                {
                    paginaAtual === primeira
                        ? (
                            <Pagination.Item key={primeira} active={true}>
                                {primeira}
                            </Pagination.Item>
                        ) : (
                            <LinkContainer to={`${URL_PAGINA}${QUERY_STRING}&pagina=${primeira}`}
                                           onClick={handleNavegacao(primeira)}>
                                <Pagination.Item key={primeira} active={false}>
                                    {primeira}
                                </Pagination.Item>
                            </LinkContainer>
                        )
                }

                {
                    antAnterior > primeira+1
                        ? <Pagination.Ellipsis key={antAnterior-1} disabled/>
                        : ""
                }

                {
                    antAnterior > primeira
                        ? (
                            <LinkContainer to={URL_PAGINA+QUERY_STRING+"&pagina="+antAnterior}
                                           onClick={handleNavegacao(antAnterior)}>
                                <Pagination.Item key={antAnterior} href={URL_PAGINA+QUERY_STRING+"&pagina="+antAnterior}>
                                    {antAnterior}
                                </Pagination.Item>
                            </LinkContainer>
                        )
                        : ""
                }

                {
                    anterior > primeira
                        ? (
                            <LinkContainer to={URL_PAGINA+QUERY_STRING+"&pagina="+anterior}
                                           onClick={handleNavegacao(anterior)}>
                                <Pagination.Item key={anterior} href={URL_PAGINA+QUERY_STRING+"&pagina="+anterior}>
                                    {anterior}
                                </Pagination.Item>
                            </LinkContainer>
                        )
                        : ""
                }

                {
                    paginaAtual !== primeira && paginaAtual !== ultima
                        ? (
                            <Pagination.Item key={paginaAtual} active>
                                {paginaAtual}
                            </Pagination.Item>
                        )
                        : ""
                }

                {
                    proxima < ultima
                        ? (
                            <LinkContainer to={URL_PAGINA+QUERY_STRING+"&pagina="+proxima}
                                           onClick={handleNavegacao(proxima)}>
                                <Pagination.Item key={proxima} href={URL_PAGINA+QUERY_STRING+"&pagina="+proxima}>
                                    {proxima}
                                </Pagination.Item>
                            </LinkContainer>
                        )
                        : ""
                }

                {
                    proxProxima < ultima
                        ? (
                            <LinkContainer to={URL_PAGINA+QUERY_STRING+"&pagina="+proxProxima}
                                           onClick={handleNavegacao(proxProxima)}>
                                <Pagination.Item key={proxProxima} >
                                    {proxProxima}
                                </Pagination.Item>
                            </LinkContainer>
                        )
                        : ""
                }

                {
                    proxProxima < ultima-1
                        ? <Pagination.Ellipsis key={proxProxima+1} disabled/>
                        : ""
                }

                {
                    ultima > primeira
                        ? (
                            <LinkContainer to={linkUltima} onClick={handleNavegacao(ultima)}>
                                <Pagination.Item key={ultima} active={paginaAtual === ultima} href={linkUltima}>
                                    {ultima}
                                </Pagination.Item>
                            </LinkContainer>
                        )
                        : ""
                }
            </>
        );
    }

    return (
        <nav>
            <Pagination>
                {paginacao(props.quantidadePaginas, props.paginaAtual) ?? ""}
            </Pagination>
        </nav>
    );
}
