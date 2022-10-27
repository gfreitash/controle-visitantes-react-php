import React, {useState} from "react";
import {Form, Modal} from "react-bootstrap";

import useInvalidSessionHandler from "../hooks/useInvalidSessionHandler";
import useAxiosPrivate from "../hooks/useAxiosPrivate";
import fileDownload from "js-file-download";

import "../assets/css/modal.css";

export async function emitirRelatorioVisitante({axios, dataInicio, dataFim, status}) {
    let url = "/relatorio?relatorio=visitantes";
    if (status) {
        url += `&status=${status}`;
    }
    if (dataInicio) {
        url += `&dataInicio=${dataInicio}`;
    }
    if (dataFim) {
        url += `&dataFim=${dataFim}`;
    }

    try {
        const resposta = await axios.get(url, {responseType: "blob"});
        let nomeArquivo =  resposta.headers['content-disposition'].split("filename=")[1];
        nomeArquivo = decodeURI(nomeArquivo);

        fileDownload(resposta.data, nomeArquivo, "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    } catch (error) {
        if (error.response.status === 401) {
            throw error;
        } else {
            console.log(error);
        }
    }
}

export default function ModalRelatorioVisitante({exibir, onFechar}) {
    const axios = useAxiosPrivate();
    const handleInvalidSession = useInvalidSessionHandler();

    const [form, setForm] = useState({
        dataInicio: "",
        dataFim: "",
        status: ""
    });
    const [checks, setChecks] = useState({
        dataInicio: false,
        dataFim: false,
        status: false
    });

    const handleStatusChange = (event) => {
        if (event.target.checked) {
            setChecks({
                ...checks,
                status: true,
                dataInicio: true
            })
        } else {
            setChecks({
                ...checks,
                status: false
            })
            setForm({
                ...form,
                status: "cadastrados"
            })
        }
    }

    const handleDataInicioChange = (event) => {
        if (event.target.checked) {
            setChecks({
                ...checks,
                dataInicio: true
            })
        } else {
            if (checks.status && form.status === "ativos" && !checks.dataFim) {
                alert("É necessário selecionar alguma data ao emitir um relatório de visitantes ativos");
            } else {
                setChecks({
                    ...checks,
                    dataInicio: false
                })
            }
        }
    }

    const handleDataFimChange = (event) => {
        if (event.target.checked) {
            setChecks({
                ...checks,
                dataFim: true
            })
        } else {
            if (checks.status && form.status === "ativos" && !checks.dataInicio) {
                alert("É necessário selecionar alguma data ao emitir um relatório de visitantes ativos");
            } else {
                setChecks({
                    ...checks,
                    dataFim: false
                })
            }
        }
    }

    const limparForm = () => {
        setForm({
            dataInicio: "",
            dataFim: "",
            status: ""
        });
        setChecks({
            dataInicio: false,
            dataFim: false,
            status: false
        });
    }

    const handleFechar = () => {
        limparForm();
        onFechar();
    }

    const handleEmissao = async () => {
        const args = {
            axios: axios,
            dataInicio: checks.dataInicio ? form.dataInicio : null,
            dataFim: checks.dataFim ? form.dataFim : null,
            status: checks.status ? form.status : null
        }

        try {
            await emitirRelatorioVisitante(args);
            handleFechar();
        } catch (error) {
            if (error.response.status === 401) {
                handleInvalidSession(error);
            }
        }
    }

    return (
        <Modal
            show={exibir}
            onHide={handleFechar}
            backdrop="static"
            keyboard={false}
            dialogClassName="modal-ln"
        >
            <Modal.Header closeButton>
                <Modal.Title>Emitir relatório de visitantes</Modal.Title>
            </Modal.Header>

            <Modal.Body>
                <section className="d-flex align-items-center justify-content-between" id="relatorioVisitantes">
                    <div className="d-flex flex-column">
                        <div className="d-flex flex-row">
                            <Form.Check name="porStatus" id="porStatus"
                                        checked={checks.status} type="checkbox" className="me-2"
                                        onChange={handleStatusChange}
                            />
                            <label htmlFor="porStatus">Por status?</label>
                        </div>
                        <div className="d-flex flex-row">
                            <Form.Select disabled={!checks.status} value={form.status} name="status" id="status"
                                         onChange={(event) => setForm({...form, status: event.target.value})}
                            >
                                <option value="cadastrados">Cadastrados</option>
                                <option value="ativos">Ativos</option>
                            </Form.Select>
                        </div>
                    </div>

                    <div className="d-flex flex-column">
                        <div className="d-flex flex-row">
                            <Form.Check name="dataInicioCheck" id="dataInicioCheck"
                                        checked={checks.dataInicio} type="checkbox" className="me-2"
                                        onChange={handleDataInicioChange}
                            />
                            <label htmlFor="dataInicioCheck">Data Início?</label>
                        </div>
                        <div className="d-flex flex-row">
                            <Form.Control type="date" disabled={!checks.dataInicio} value={form.dataInicio}
                                            onChange={(event) => setForm({...form, dataInicio: event.target.value})}
                            />
                        </div>
                    </div>

                    <div className="d-flex flex-column">
                        <div className="d-flex flex-row">
                            <Form.Check name="dataFimCheck" id="dataFimCheck"
                                        checked={checks.dataFim} type="checkbox" className="me-2"
                                        onChange={handleDataFimChange}
                            />
                            <label htmlFor="dataFimCheck">Data Fim?</label>
                        </div>
                        <div className="d-flex flex-row">
                            <Form.Control type="date" disabled={!checks.dataFim} value={form.dataFim}
                                            onChange={(event) => setForm({...form, dataFim: event.target.value})}
                            />
                        </div>
                    </div>
                </section>
            </Modal.Body>

            <Modal.Footer>
                <button className="btn btn-secondary" onClick={handleFechar}>Cancelar</button>
                <button className="btn btn-primary" onClick={handleEmissao}>Emitir</button>
            </Modal.Footer>
        </Modal>
    )
}
