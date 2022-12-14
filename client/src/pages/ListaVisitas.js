import React, {useEffect, useState} from "react";
import {Link, useParams} from "react-router-dom";

import useInvalidSessionHandler from "../hooks/useInvalidSessionHandler";
import useAuth from "../hooks/useAuth";
import useAxiosPrivate from "../hooks/useAxiosPrivate";
import {ProvedorLista} from "../context/ProvedorLista";

import {Modal} from "react-bootstrap";
import {FontAwesomeIcon} from "@fortawesome/react-fontawesome";
import {
    faBuildingUser, faClipboard, faDoorOpen, faLock, faNotesMedical,
    faPenToSquare,
    faPersonWalkingDashedLineArrowRight
} from "@fortawesome/free-solid-svg-icons";

import {mascaraCPF} from "../assets/js/dados-visitante";
import Titulo from "../components/Titulo";
import Alerta from "../components/Alerta";
import ListaItens, {TableData, TableHeader} from "../components/ListaItens";
import useQuery from "../hooks/useQuery";

export default function ListaVisitas() {
    const query = useQuery();
    const axios = useAxiosPrivate();
    const handleInvalidSession = useInvalidSessionHandler();
    const {auth} = useAuth();
    const {id} = useParams();

    const urls = {
        pagina: `/lista-visitas/${id}`,
        backend: `/visita/${id}`,
        adicionarItem: "/nova-visita"
    }

    const [exibirModal, setExibirModal] = useState(false);
    const [objetoModal, setObjetoModal] = useState({});
    const [alerta, setAlerta] = useState({tipo: "", mensagem: ""});
    const [parametro, setParametro] = useState({
        dataInicio: query.get("dataInicio") ?? "",
        dataFim: query.get("dataFim") ?? ""
    });

    const titulo = (id) => {
        switch (id) {
            case "abertas":
                return "Lista de visitas abertas";
            case "fechadas":
                return "Lista de visitas finalizadas";
            case "todas":
                return "Lista de todas as visitas";
            default:
                return "Visitas";
        }
    }

    useEffect(() => {
        let dataInicio = query.get("dataInicio") ?? "";
        let dataFim = query.get("dataFim") ?? "";

        setParametro({dataInicio, dataFim});
    }, [query]);

    const handleAbrirModal = (visita) => {
        return () => {
            setObjetoModal(visita);
            setExibirModal(true);
        }
    }

    const handleFecharModal = () => {
        setObjetoModal({});
        setExibirModal(false);
    }

    const handleFinalizarVisita = async () => {
        try {
            const resposta = await axios.delete(`${urls.backend}?id=${objetoModal.id}&idUsuario=${auth.id}`);

            if (resposta.status === 200) {
                setAlerta({
                    tipo: "success",
                    mensagem: "Visita finalizada com sucesso!"
                });
            } else {
                setAlerta({
                    tipo: "danger",
                    mensagem: `Erro ao finalizar visita de n??mero ${objetoModal.id}!`
                })
            }
        } catch (erro) {
            switch (erro.response.status) {
                case 400:
                    setAlerta({
                        tipo: "danger",
                        mensagem: "N??o foi poss??vel finalizar a visita. Dados inv??lidos"
                    });
                    break;
                case 401:
                    handleInvalidSession();
                    break;
                case 404:
                    setAlerta({
                        tipo: "danger",
                        mensagem: "N??o foi poss??vel finalizar a visita. Visita n??o encontrada"
                    });
                    break;
                case 500:
                    setAlerta({
                        tipo: "danger",
                        mensagem: "N??o foi poss??vel finalizar a visita. Erro interno do servidor"
                    });
                    break;
                case 0:
                    setAlerta({
                        tipo: "danger",
                        mensagem: "N??o foi poss??vel finalizar a visita. Erro de conex??o"
                    });
                    break;
                default:
                    setAlerta({
                        tipo: "danger",
                        mensagem: "N??o foi poss??vel finalizar a visita. Erro desconhecido"
                    });

            }
        }

        setObjetoModal({});
        setExibirModal(false);
    }

    const modalFinalizarVisita = (
        <Modal show={exibirModal}
               onHide={() => setExibirModal(false)}
               backdrop="static"
               keyboard={false}>
            <Modal.Header closeButton>
                <Modal.Title>Finalizar visita</Modal.Title>
            </Modal.Header>
            <Modal.Body>
                <p>Tem certeza que deseja finalizar a visita de n??mero <b>{objetoModal.id}</b>?</p>
                <hr/>
                <p><b>Visitante:</b> {objetoModal.nome} <br/>
                    <b>CPF:</b> {objetoModal.cpf ? mascaraCPF(objetoModal.cpf) : ""} <br/>
                    <b>Data da visita:</b> {new Date(Date.parse(objetoModal.data_visita)).toLocaleString()} <br/>
                    <b>Sala da visita:</b> {objetoModal.sala_visita}
                </p>
            </Modal.Body>
            <Modal.Footer>
                <button className="btn btn-secondary" onClick={handleFecharModal}>Cancelar</button>
                <button className="btn btn-primary" onClick={handleFinalizarVisita}>Finalizar</button>
            </Modal.Footer>
        </Modal>
    )

    const tableHeaders = (
        <>
            <TableHeader id="id" tipo="limitado" titulo="N??mero da visita"/>
            <TableHeader id="cpf" tipo="limitado" titulo="CPF"/>
            <TableHeader id="nome" tipo="ilimitado" titulo="Nome"/>
            <TableHeader id="sala_visita" tipo="limitado" titulo="Sala de visita"/>
            <TableHeader id="motivo_visita" tipo="ilimitado" titulo="Motivo da visita"/>
            <TableHeader id="data_visita" tipo="data" titulo="Data da visita"/>
            <TableHeader id="finalizada_em" tipo="data" titulo="Finalizada em"/>
            <TableHeader id="detalhes" tipo="icone" icone={faBuildingUser} tooltip={"Detalhes da visita"}/>
            <TableHeader id="nova_observacao" tipo="icone" icone={faClipboard} tooltip={"Nova observa????o"}/>
            <TableHeader id="finalizar" tipo="icone" icone={faDoorOpen} tooltip={"Status da visita"}/>
        </>
    )

    const mapFunction = (visita) => {
        const data_visita = visita.data_visita
            ? new Date(Date.parse(visita.data_visita))
            : null;
        const finalizada_em = visita.finalizada_em
            ? new Date(Date.parse(visita.finalizada_em))
            : null;

        return (
            <tr key={visita.id}>
                <TableData tipo="limitado">N?? {visita.id}</TableData>
                <TableData tipo="limitado">{mascaraCPF(visita.cpf)}</TableData>
                <TableData tipo="ilimitado">{visita.nome}</TableData>
                <TableData tipo="limitado">{visita.sala_visita}</TableData>
                <TableData tipo="ilimitado">{visita.motivo_visita}</TableData>
                <TableData tipo="data">{data_visita?.toLocaleString()}</TableData>
                <TableData tipo="data">{finalizada_em?.toLocaleString()}</TableData>

                <TableData tipo="icone" tooltip="Detalhes da visita">
                    <Link to={`/visita?id=${visita.id}`}>
                        <FontAwesomeIcon icon={faPenToSquare}/>
                    </Link>
                </TableData>

                <TableData tipo="icone" tooltip="Nova observa????o">
                    <Link to={`/visita?id=${visita.id}&adicionarObservacao=true`}>
                        <FontAwesomeIcon icon={faNotesMedical}/>
                    </Link>
                </TableData>

                {!visita.finalizada_em
                    ? (
                        <TableData tipo="icone" tooltip="Finalizar visita">
                            <FontAwesomeIcon icon={faPersonWalkingDashedLineArrowRight}
                                             style={{cursor: "pointer"}}
                                             onClick={handleAbrirModal(visita)}/>
                        </TableData>
                    )
                    : (
                        <TableData tipo="icone" tooltip="Visita finalizada">
                            <FontAwesomeIcon icon={faLock}/>
                        </TableData>
                    )
                }
            </tr>
        )
    }

    return (
        <>
            <Titulo>{titulo(id)}</Titulo>
            <hr/>

            <Alerta alerta={alerta} setAlerta={setAlerta}/>
            <ProvedorLista>
                <ListaItens
                    urls={urls}
                    labelAdicionar="Cadastrar nova visita"
                    parametro={parametro}
                    defaultOrdenar="data_visita"
                    defaultOrdem="DESC"
                    tableHeaders={tableHeaders}
                    mapFunction={mapFunction}
                    paginacao
                    tableHover
                    tableStriped
                    tableDark
                />
            </ProvedorLista>
            {modalFinalizarVisita}
        </>
    )
}
