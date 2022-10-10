import React, {useEffect} from "react";
import {Form} from "react-bootstrap";
import useAxiosPrivate from "../hooks/useAxiosPrivate";
import "../assets/css/dados-registro.css";

export default function RegistroVisitante(props) {
    const axios = useAxiosPrivate();

    const [formData, setFormData] = React.useState({
        cadastradoPor: "",
        cadastradoEm: "",
        modificadoPor: "",
        modificadoEm: "",
    });

    useEffect(() => {
        let isMounted = true;
        let cadastradoPor = "";
        let modificadoPor = "";
        const controlador = new AbortController();

        async function getCadastradoPor() {
            try {
                const response = await axios.get(`/usuario?id=${props.cadastradoPor}`, {signal: controlador.signal});
                return response.data.nome;
            } catch (error) {
                if (error.code === "ERR_CANCELED")
                    return;
                console.log(error);
            }
        }
        async function getModificadoPor() {
            try {
                const response = await axios.get(`/usuario?id=${props.modificadoPor}`, {signal: controlador.signal});
                return response.data.nome;
            } catch (error) {
                if (error.code === "ERR_CANCELED")
                    return;
                console.log(error);
            }
        }

        async function preencherCampos() {
            if(props.cadastradoEm || props.modificadoEm) {
                if (props.cadastradoPor) {
                    cadastradoPor = await getCadastradoPor();
                }
                if (props.modificadoPor) {
                    modificadoPor = await getModificadoPor();
                }

                if (isMounted) {
                    setFormData({
                        cadastradoPor: cadastradoPor ?? "",
                        cadastradoEm: props.cadastradoEm ?? "",
                        modificadoPor: modificadoPor ?? "",
                        modificadoEm: props.modificadoEm ?? ""
                    });
                }
            }
        }

        preencherCampos();

        return () => {
            isMounted = false;
            controlador.abort();
        }
    }, [props]);

    return (
        <div className="form-linha">
            <div className="registro__grupo width--47_5">
                <Form.Group controlId="cadastradoPor" className="campo">
                    <Form.Label>Visitante registrado por:</Form.Label>
                    <Form.Control className="form-control-sm" disabled type="text" name="cadastradoPor"
                                  value={formData.cadastradoPor}/>
                </Form.Group>
                <Form.Group controlId="cadastradoEm" className="campo">
                    <Form.Label>Data do cadastro:</Form.Label>
                    <Form.Control className="form-control-sm" disabled type="datetime-local" name="cadastradoEm"
                                  value={formData.cadastradoEm}/>
                </Form.Group>
            </div>

            <div className="registro__grupo width--47_5">
                <Form.Group controlId="modificadoPor" className="campo">
                    <Form.Label>Última modificação por:</Form.Label>
                    <Form.Control className="form-control-sm" disabled type="text" name="modificadoPor"
                                  value={formData.modificadoPor}/>
                </Form.Group>
                <Form.Group controlId="modificadoEm" className="campo">
                    <Form.Label>Última modificação em:</Form.Label>
                    <Form.Control className="form-control-sm" disabled type="datetime-local" name="modificadoEm"
                                  value={formData.modificadoEm}/>
                </Form.Group>
            </div>
        </div>
    )
}
