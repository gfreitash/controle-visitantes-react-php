import React, {useState} from "react";
import ListaItens, {TableData, TableHeader} from "../components/ListaItens";
import {
    faBuildingUser, faLock,
    faPenToSquare,
    faPersonWalking,
    faPersonWalkingDashedLineArrowRight
} from "@fortawesome/free-solid-svg-icons";
import {mascaraCPF} from "../assets/js/modules/dados-visitante";
import {Link, useParams} from "react-router-dom";
import {FontAwesomeIcon} from "@fortawesome/react-fontawesome";
import Titulo from "../components/Titulo";
import {ProvedorLista} from "../context/ProvedorLista";
import {Modal} from "react-bootstrap";
import useAxiosPrivate from "../hooks/useAxiosPrivate";
import Alerta from "../components/Alerta";
import useInvalidSessionHandler from "../hooks/useInvalidSessionHandler";
import useAuth from "../hooks/useAuth";

export default function ListaVisitas() {
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
                    mensagem: `Erro ao finalizar visita de número ${objetoModal.id}!`
                })
            }
        } catch (erro) {
            switch (erro.response.status) {
                case 400:
                    setAlerta({
                        tipo: "danger",
                        mensagem: "Não foi possível finalizar a visita. Dados inválidos"
                    });
                    break;
                case 401:
                    handleInvalidSession();
                    break;
                case 404:
                    setAlerta({
                        tipo: "danger",
                        mensagem: "Não foi possível finalizar a visita. Visita não encontrada"
                    });
                    break;
                case 500:
                    setAlerta({
                        tipo: "danger",
                        mensagem: "Não foi possível finalizar a visita. Erro interno do servidor"
                    });
                    break;
                case 0:
                    setAlerta({
                        tipo: "danger",
                        mensagem: "Não foi possível finalizar a visita. Erro de conexão"
                    });
                    break;
                default:
                    setAlerta({
                        tipo: "danger",
                        mensagem: "Não foi possível finalizar a visita. Erro desconhecido"
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
                <p>Tem certeza que deseja finalizar a visita de número {objetoModal.id}?</p>
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
            <TableHeader id="id" tipo="limitado" titulo="Número da visita"/>
            <TableHeader id="cpf" tipo="limitado" titulo="CPF"/>
            <TableHeader id="nome" tipo="ilimitado" titulo="Nome"/>
            <TableHeader id="sala_visita" tipo="limitado" titulo="Sala de visita"/>
            <TableHeader id="motivo_visita" tipo="ilimitado" titulo="Motivo da visita"/>
            <TableHeader id="data_visita" tipo="data" titulo="Data da visita"/>
            <TableHeader id="finalizada_em" tipo="data" titulo="Finalizada em"/>
            <TableHeader id="detalhes" tipo="icone" icone={faBuildingUser}/>
            <TableHeader id="finalizar" tipo="icone" icone={faPersonWalking}/>
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
                <TableData tipo="limitado">Nº {visita.id}</TableData>
                <TableData tipo="limitado">{mascaraCPF(visita.cpf)}</TableData>
                <TableData tipo="ilimitado">{visita.nome}</TableData>
                <TableData tipo="limitado">{visita.sala_visita}</TableData>
                <TableData tipo="ilimitado">{visita.motivo_visita}</TableData>
                <TableData tipo="data">{data_visita?.toLocaleString()}</TableData>
                <TableData tipo="data">{finalizada_em?.toLocaleString()}</TableData>

                <TableData tipo="icone">
                    <Link to={`/visita?id=${visita.id}`}>
                        <FontAwesomeIcon icon={faPenToSquare}/>
                    </Link>
                </TableData>
                <TableData tipo="icone">
                    {!visita.finalizada_em
                        ? (
                            <FontAwesomeIcon icon={faPersonWalkingDashedLineArrowRight}
                                             style={{cursor: "pointer"}}
                                             onClick={handleAbrirModal(visita)}/>
                        )
                        : (
                            <FontAwesomeIcon icon={faLock}/>
                        )
                    }
                </TableData>
            </tr>
        )
    }

    return (
        <>
            <Titulo>Lista de Visitas</Titulo>
            <hr/>
            <Alerta alerta={alerta} setAlerta={setAlerta}/>
            <ProvedorLista>
                <ListaItens
                    urls={urls}
                    defaultOrdenar="data_visita"
                    defaultOrdem="DESC"
                    placeholderPesquisa="Insira um cpf, nome ou data da visita"
                    tableHeaders={tableHeaders}
                    mapFunction={mapFunction}
                />
            </ProvedorLista>
            {modalFinalizarVisita}
        </>
    )
}
