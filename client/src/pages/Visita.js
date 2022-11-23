import React, {useEffect, useState} from "react";

import useQuery from "../hooks/useQuery";
import "../assets/css/lista-itens.css";

import DadosVisitante from "../components/DadosVisitante";
import DadosVisita from "../components/DadosVisita";
import RegistroVisita from "../components/RegistroVisita";
import FotoVisitante from "../components/FotoVisitante";
import TituloVisita from "../components/TituloVisita";
import Alerta from "../components/Alerta";
import ModalVisita from "../components/ModalVisita";
import ListaItens, {TableData, TableHeader} from "../components/ListaItens";
import {FontAwesomeIcon} from "@fortawesome/react-fontawesome";
import {faPenToSquare, faSquarePlus} from "@fortawesome/free-solid-svg-icons";
import {ProvedorLista} from "../context/ProvedorLista";
import ModalObservacao from "../components/ModalObservacao";

export default function Visita() {
    const query = useQuery();

    const [id] = useState(query.get("id") ?? "");
    const [cpf, setCpf] = useState("");
    const [status, setStatus] = useState("");
    const [visita, setVisita] = useState({});
    const [visitante, setVisitante] = useState({});

    const [exibirModalFinalizar, setExibirModalFinalizar] = useState(false);
    const [exibirModalEditar, setExibirModalEditar] = useState(false);
    const [exibirModalAdicionarObservacao, setExibirModalAdicionarObservacao] = useState(query.get("adicionarObservacao") ?? false);
    const [exibirModalEditarObservacao, setExibirModalEditarObservacao] = useState(false);

    const [alerta, setAlerta] = useState({tipo: "", mensagem: ""});
    const [alertaObservacao, setAlertaObservacao] = useState({tipo: "", mensagem: ""});

    const [parametro, setParametro] = useState({id});
    const [objetoModal, setObjetoModal] = useState({});
    const [recarregar, setRecarregar] = useState(0);

    const onCpfValido = (codigoResposta, visitante) => {
        if (codigoResposta === 200) {
            setVisitante(visitante);
        }
    }

    const onVisitaEncontrada = (visita) => {
        setVisita(visita);
    }

    const onSucessoAdicionarObservacao = () => {
        setAlertaObservacao({tipo: "success", mensagem: "Observação adicionada com sucesso!"});
        setRecarregar(recarregar + 1);
    }

    const onSucessoEditarObservacao = () => {
        setAlertaObservacao({tipo: "success", mensagem: "Observação editada com sucesso!"});
        setRecarregar(recarregar + 1);
    }

    const onFalhaAdicionarObservacao = (e) => {
        setAlertaObservacao({
            tipo: "danger",
            mensagem: `Falha ao adicionar observação! ${e.response?.data?.error}`
        });
    }

    const onFalhaEditarObservacao = (e) => {
        setAlertaObservacao({
            tipo: "danger",
            mensagem: `Falha ao editar observação! ${e.response?.data?.error}`
        });
    }

    const handleAdicionarObservacao = () => {
        return () => {
            setAlertaObservacao({tipo: "", mensagem: ""});
            setExibirModalAdicionarObservacao(true);
        }
    }

    const handleEdicaoObservacao = (observacao) => {
        return () => {
            setAlertaObservacao({tipo: "", mensagem: ""});
            setObjetoModal(observacao);
            setExibirModalEditarObservacao(true);
        }
    }

    useEffect(() => {
        setCpf(visita.cpf);

        if(visita.finalizada_em) {
            setStatus("fechada");
        } else if(visita.data_visita) {
            setStatus("aberta");
        }
    }, [visita]);

    useEffect(() => {
        setParametro({id});
    }, [query]);

    const tableHeaders = (
        <>
            <TableHeader id="id" tipo="limitado-menor" titulo="ID" />
            <TableHeader id="observacao" tipo="ilimitado" titulo="Observação"/>
            <TableHeader id="adicionada_por" tipo="limitado" titulo="Adicionada por"/>
            <TableHeader id="adicionada_em" tipo="data" titulo="Adicionada em"/>
            <TableHeader id="nova_observacao" tipo="icone" icone={faSquarePlus} tamanho="xl" iconePreto
                         tooltip="Adicionar observação" cursor="pointer" handleClick={handleAdicionarObservacao()}/>
        </>
    )

    const mapFunction = (observacao) => {
        const adicionada_em = new Date(Date.parse(observacao.adicionada_em));

        return (
            <tr key={observacao.id}>
                <TableData tipo="limitado-menor">{observacao.id}</TableData>
                <TableData tipo="ilimitado">{observacao.observacao}</TableData>
                <TableData tipo="limitado-maior">{observacao.adicionada_por.nome}</TableData>
                <TableData tipo="data">{adicionada_em.toLocaleString()}</TableData>
                <TableData tipo="icone" tooltip="Editar observação" cursor="pointer">
                    <FontAwesomeIcon icon={faPenToSquare} onClick={handleEdicaoObservacao(observacao)}/>
                </TableData>
            </tr>
        );
    }

    return (
        <div className="form-fieldset">
            <Alerta alerta={alerta} setAlerta={setAlerta}/>
            <TituloVisita status={status} id={id} onEditar={()=>setExibirModalEditar(true)} onFinalizar={()=>setExibirModalFinalizar(true)}/>
            <hr/>
            <form className="form" id='form' encType='multipart/form-data'>
                <div className="form-wrapper">
                    <div className="width--95">
                        <DadosVisitante estado="disabled" estadoCpf="disabled" cpf={cpf}
                                        onCpfValido={onCpfValido} buscarDados={!visitante.cpf}/>
                        <hr/>
                        <DadosVisita id={id} disabled={!exibirModalEditar} onVisitaEncontrada={onVisitaEncontrada} status={status}/>
                        <hr/>
                        <RegistroVisita
                            cadastradaEm={visita.data_visita}
                            cadastradaPor={visita.cadastrada_por}
                            modificadaEm={visita.modificada_em}
                            modificadaPor={visita.modificada_por}
                            finalizadaEm={visita.finalizada_em}
                            finalizadaPor={visita.finalizada_por}
                        />
                    </div>
                </div>
                <FotoVisitante foto={visitante.foto}/>
            </form>
            <hr/>
            <ProvedorLista>
                <Alerta alerta={alertaObservacao} setAlerta={setAlertaObservacao}/>
                <section className="tabela-wrapper">
                    <ListaItens
                        urls={{pagina: "/visita", backend: `/observacao/visita`}}
                        recarregar={recarregar}
                        parametro={parametro}
                        defaultOrdenar="adicionada_em"
                        defaultOrdem="DESC"
                        tableHeaders={tableHeaders}
                        mapFunction={mapFunction}
                        semLimite
                        tableHover
                        tableDivider
                    />
                </section>
            </ProvedorLista>
            <ModalVisita
                editar={{exibirModalEditar: exibirModalEditar, setExibirModalEditar: setExibirModalEditar}}
                finalizar={{exibirModalFinalizar: exibirModalFinalizar, setExibirModalFinalizar: setExibirModalFinalizar}}
                visita={visita}
                setAlerta={setAlerta}
                setStatus={setStatus}
            />
            <ModalObservacao
                modo="adicionar"
                exibir={exibirModalAdicionarObservacao}
                onFechar={() => setExibirModalAdicionarObservacao(false)}
                onSucesso={onSucessoAdicionarObservacao}
                onFalha={onFalhaAdicionarObservacao}
                idVisita={id}
            />
            <ModalObservacao
                modo="editar"
                exibir={exibirModalEditarObservacao}
                onFechar={() => setExibirModalEditarObservacao(false)}
                onSucesso={onSucessoEditarObservacao}
                onFalha={onFalhaEditarObservacao}
                id={objetoModal.id}
                observacao={objetoModal.observacao}
            />
        </div>
    )
}
