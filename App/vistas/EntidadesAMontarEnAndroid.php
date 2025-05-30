<!-- SOLICITUD DE UNA NUEVA BARAJA -->
(IP/DOMINIO)/PartidaControlador/nuevaPartida

<!-- ME DEVUELVE AL EMPEZAR LA PARTIDA -->
    {
        "baraja": {
            "success": true,
            "deck_id": "4aheyw9w2nzi",
            "remaining": 41,
            "shuffled": true
        },
        "jugadores": [
            "jugador1",
            "jugador2"
        ],
        "reparto": {
            "success": true,
            "jugadores": [
                "jugador1",
                "jugador2"
            ]
        }
    }

    // To parse this JSON data, do
    //
    //     final responseNuevaPartida = responseNuevaPartidaFromJson(jsonString);

    import 'dart:convert';

    ResponseNuevaPartida responseNuevaPartidaFromJson(String str) => ResponseNuevaPartida.fromJson(json.decode(str));

    String responseNuevaPartidaToJson(ResponseNuevaPartida data) => json.encode(data.toJson());

    class ResponseNuevaPartida {
        Baraja baraja;
        List<String> jugadores;
        Reparto reparto;

        ResponseNuevaPartida({
            required this.baraja,
            required this.jugadores,
            required this.reparto,
        });

        factory ResponseNuevaPartida.fromJson(Map<String, dynamic> json) => ResponseNuevaPartida(
            baraja: Baraja.fromJson(json["baraja"]),
            jugadores: List<String>.from(json["jugadores"].map((x) => x)),
            reparto: Reparto.fromJson(json["reparto"]),
        );

        Map<String, dynamic> toJson() => {
            "baraja": baraja.toJson(),
            "jugadores": List<dynamic>.from(jugadores.map((x) => x)),
            "reparto": reparto.toJson(),
        };
    }

    class Baraja {
        bool success;
        String deckId;
        int remaining;
        bool shuffled;

        Baraja({
            required this.success,
            required this.deckId,
            required this.remaining,
            required this.shuffled,
        });

        factory Baraja.fromJson(Map<String, dynamic> json) => Baraja(
            success: json["success"],
            deckId: json["deck_id"],
            remaining: json["remaining"],
            shuffled: json["shuffled"],
        );

        Map<String, dynamic> toJson() => {
            "success": success,
            "deck_id": deckId,
            "remaining": remaining,
            "shuffled": shuffled,
        };
    }

    class Reparto {
        bool success;
        List<String> jugadores;

        Reparto({
            required this.success,
            required this.jugadores,
        });

        factory Reparto.fromJson(Map<String, dynamic> json) => Reparto(
            success: json["success"],
            jugadores: List<String>.from(json["jugadores"].map((x) => x)),
        );

        Map<String, dynamic> toJson() => {
            "success": success,
            "jugadores": List<dynamic>.from(jugadores.map((x) => x)),
        };
    }

<!-- SOLICITUD DE la mano de los jugadores -->

<!-- JSON para enviar la mano de los jugadores -->
    {
        "jugador1": {
            "idCarta": "5S",
            "suit": "SPADES",
            "value": 5,
            "primeroenJugar": true
        },
        "jugador2": {
            "idCarta": "5S",
            "suit": "SPADES",
            "value": 5,
            "primeroenJugar": false
        }
    }

    // To parse this JSON data, do
    //
    //     final jugarMano = jugarManoFromJson(jsonString);

    import 'dart:convert';

    JugarMano jugarManoFromJson(String str) => JugarMano.fromJson(json.decode(str));

    String jugarManoToJson(JugarMano data) => json.encode(data.toJson());

    class JugarMano {
        Jugador jugador1;
        Jugador jugador2;

        JugarMano({
            required this.jugador1,
            required this.jugador2,
        });

        factory JugarMano.fromJson(Map<String, dynamic> json) => JugarMano(
            jugador1: Jugador.fromJson(json["jugador1"]),
            jugador2: Jugador.fromJson(json["jugador2"]),
        );

        Map<String, dynamic> toJson() => {
            "jugador1": jugador1.toJson(),
            "jugador2": jugador2.toJson(),
        };
    }

    class Jugador {
        String idCarta;
        String suit;
        int value;
        bool primeroenJugar;

        Jugador({
            required this.idCarta,
            required this.suit,
            required this.value,
            required this.primeroenJugar,
        });

        factory Jugador.fromJson(Map<String, dynamic> json) => Jugador(
            idCarta: json["idCarta"],
            suit: json["suit"],
            value: json["value"],
            primeroenJugar: json["primeroenJugar"],
        );

        Map<String, dynamic> toJson() => {
            "idCarta": idCarta,
            "suit": suit,
            "value": value,
            "primeroenJugar": primeroenJugar,
        };
    }


<!-- Me devuelve Ver Triunfo -->
    {
        "cartas": [
            {
                "code": "5D",
                "image": "https://deckofcardsapi.com/static/img/5D.png",
                "images": {
                    "svg": "https://deckofcardsapi.com/static/img/5D.svg",
                    "png": "https://deckofcardsapi.com/static/img/5D.png"
                },
                "value": "5",
                "suit": "DIAMONDS"
            }
        ],
        "success": true
    }
    // To parse this JSON data, do
    //
    //     final responseVerTriunfo = responseVerTriunfoFromJson(jsonString);

    import 'dart:convert';

    ResponseVerTriunfo responseVerTriunfoFromJson(String str) => ResponseVerTriunfo.fromJson(json.decode(str));

    String responseVerTriunfoToJson(ResponseVerTriunfo data) => json.encode(data.toJson());

    class ResponseVerTriunfo {
        List<Carta> cartas;
        bool success;

        ResponseVerTriunfo({
            required this.cartas,
            required this.success,
        });

        factory ResponseVerTriunfo.fromJson(Map<String, dynamic> json) => ResponseVerTriunfo(
            cartas: List<Carta>.from(json["cartas"].map((x) => Carta.fromJson(x))),
            success: json["success"],
        );

        Map<String, dynamic> toJson() => {
            "cartas": List<dynamic>.from(cartas.map((x) => x.toJson())),
            "success": success,
        };
    }

    class Carta {
        String code;
        String image;
        Images images;
        String value;
        String suit;

        Carta({
            required this.code,
            required this.image,
            required this.images,
            required this.value,
            required this.suit,
        });

        factory Carta.fromJson(Map<String, dynamic> json) => Carta(
            code: json["code"],
            image: json["image"],
            images: Images.fromJson(json["images"]),
            value: json["value"],
            suit: json["suit"],
        );

        Map<String, dynamic> toJson() => {
            "code": code,
            "image": image,
            "images": images.toJson(),
            "value": value,
            "suit": suit,
        };
    }

    class Images {
        String svg;
        String png;

        Images({
            required this.svg,
            required this.png,
        });

        factory Images.fromJson(Map<String, dynamic> json) => Images(
            svg: json["svg"],
            png: json["png"],
        );

        Map<String, dynamic> toJson() => {
            "svg": svg,
            "png": png,
        };
    }

<!-- Envio Ver Cartas Jugador -->

<!-- Me devuelve VER CARTAS JUGADOR -->
    {
        "cartas": [
            {
                "code": "3D",
                "image": "https://deckofcardsapi.com/static/img/3D.png",
                "images": {
                    "svg": "https://deckofcardsapi.com/static/img/3D.svg",
                    "png": "https://deckofcardsapi.com/static/img/3D.png"
                },
                "value": "3",
                "suit": "DIAMONDS"
            },
            {
                "code": "AD",
                "image": "https://deckofcardsapi.com/static/img/aceDiamonds.png",
                "images": {
                    "svg": "https://deckofcardsapi.com/static/img/aceDiamonds.svg",
                    "png": "https://deckofcardsapi.com/static/img/aceDiamonds.png"
                },
                "value": "ACE",
                "suit": "DIAMONDS"
            },
            {
                "code": "4C",
                "image": "https://deckofcardsapi.com/static/img/4C.png",
                "images": {
                    "svg": "https://deckofcardsapi.com/static/img/4C.svg",
                    "png": "https://deckofcardsapi.com/static/img/4C.png"
                },
                "value": "4",
                "suit": "CLUBS"
            },
            {
                "code": "QH",
                "image": "https://deckofcardsapi.com/static/img/QH.png",
                "images": {
                    "svg": "https://deckofcardsapi.com/static/img/QH.svg",
                    "png": "https://deckofcardsapi.com/static/img/QH.png"
                },
                "value": "QUEEN",
                "suit": "HEARTS"
            },
            {
                "code": "AC",
                "image": "https://deckofcardsapi.com/static/img/AC.png",
                "images": {
                    "svg": "https://deckofcardsapi.com/static/img/AC.svg",
                    "png": "https://deckofcardsapi.com/static/img/AC.png"
                },
                "value": "ACE",
                "suit": "CLUBS"
            }
        ],
        "success": true
    }
    // To parse this JSON data, do
    //
    //     final responseVerCartasJugador = responseVerCartasJugadorFromJson(jsonString);

    import 'dart:convert';

    ResponseVerCartasJugador responseVerCartasJugadorFromJson(String str) => ResponseVerCartasJugador.fromJson(json.decode(str));

    String responseVerCartasJugadorToJson(ResponseVerCartasJugador data) => json.encode(data.toJson());

    class ResponseVerCartasJugador {
        List<Carta> cartas;
        bool success;

        ResponseVerCartasJugador({
            required this.cartas,
            required this.success,
        });

        factory ResponseVerCartasJugador.fromJson(Map<String, dynamic> json) => ResponseVerCartasJugador(
            cartas: List<Carta>.from(json["cartas"].map((x) => Carta.fromJson(x))),
            success: json["success"],
        );

        Map<String, dynamic> toJson() => {
            "cartas": List<dynamic>.from(cartas.map((x) => x.toJson())),
            "success": success,
        };
    }

    class Carta {
        String code;
        String image;
        Images images;
        String value;
        String suit;

        Carta({
            required this.code,
            required this.image,
            required this.images,
            required this.value,
            required this.suit,
        });

        factory Carta.fromJson(Map<String, dynamic> json) => Carta(
            code: json["code"],
            image: json["image"],
            images: Images.fromJson(json["images"]),
            value: json["value"],
            suit: json["suit"],
        );

        Map<String, dynamic> toJson() => {
            "code": code,
            "image": image,
            "images": images.toJson(),
            "value": value,
            "suit": suit,
        };
    }

    class Images {
        String svg;
        String png;

        Images({
            required this.svg,
            required this.png,
        });

        factory Images.fromJson(Map<String, dynamic> json) => Images(
            svg: json["svg"],
            png: json["png"],
        );

        Map<String, dynamic> toJson() => {
            "svg": svg,
            "png": png,
        };
    }



<!--ME DEVUELVE AL JUGAR UNA CARTA-->
    {
        "success": true,
        "carta": "2H",
        "primeroenJugar": "true"
    }
    // To parse this JSON data, do
    //
    //     final responseJugarCarta = responseJugarCartaFromJson(jsonString);

    import 'dart:convert';

    ResponseJugarCarta responseJugarCartaFromJson(String str) => ResponseJugarCarta.fromJson(json.decode(str));

    String responseJugarCartaToJson(ResponseJugarCarta data) => json.encode(data.toJson());

    class ResponseJugarCarta {
        bool success;
        String carta;
        bool primeroenJugar;

        ResponseJugarCarta({
            required this.success,
            required this.carta,
            required this.primeroenJugar,
        });

        factory ResponseJugarCarta.fromJson(Map<String, dynamic> json) => ResponseJugarCarta(
            success: json["success"],
            carta: json["carta"],
            primeroenJugar: json["primeroenJugar"],
        );

        Map<String, dynamic> toJson() => {
            "success": success,
            "carta": carta,
            "primeroenJugar": primeroenJugar,
        };
    }


<!-- ME DEVUELVE AL ROBAR UNA CARTA -->
    {
        "success": true,
        "carta": "5C"
    }

    // To parse this JSON data, do
    //
    //     final responseRobarCarta = responseRobarCartaFromJson(jsonString);

    import 'dart:convert';

    ResponseRobarCarta responseRobarCartaFromJson(String str) => ResponseRobarCarta.fromJson(json.decode(str));

    String responseRobarCartaToJson(ResponseRobarCarta data) => json.encode(data.toJson());

    class ResponseRobarCarta {
        bool success;
        String carta;

        ResponseRobarCarta({
            required this.success,
            required this.carta,
        });

        factory ResponseRobarCarta.fromJson(Map<String, dynamic> json) => ResponseRobarCarta(
            success: json["success"],
            carta: json["carta"],
        );

        Map<String, dynamic> toJson() => {
            "success": success,
            "carta": carta,
        };
    }


<!-- Me devuelve el registro historico de un jugador -->
    [
    {
        "equipo1_nombres": "e a",
        "equipo2_nombres": "NombrePrueba ApellidoPrueba",
        "resultado": "Perdida",
        "num_sets": 1,
        "puntuacion_equipo1": 54,
        "puntuacion_equipo2": 3
    }
    ]

    // To parse this JSON data, do
    //
    //     final registroHistoricoJugador = registroHistoricoJugadorFromJson(jsonString);

    import 'dart:convert';

    List<RegistroHistoricoJugador> registroHistoricoJugadorFromJson(String str) => List<RegistroHistoricoJugador>.from(json.decode(str).map((x) => RegistroHistoricoJugador.fromJson(x)));

    String registroHistoricoJugadorToJson(List<RegistroHistoricoJugador> data) => json.encode(List<dynamic>.from(data.map((x) => x.toJson())));

    class RegistroHistoricoJugador {
        String equipo1Nombres;
        String equipo2Nombres;
        String resultado;
        int numSets;
        int puntuacionEquipo1;
        int puntuacionEquipo2;

        RegistroHistoricoJugador({
            required this.equipo1Nombres,
            required this.equipo2Nombres,
            required this.resultado,
            required this.numSets,
            required this.puntuacionEquipo1,
            required this.puntuacionEquipo2,
        });

        factory RegistroHistoricoJugador.fromJson(Map<String, dynamic> json) => RegistroHistoricoJugador(
            equipo1Nombres: json["equipo1_nombres"],
            equipo2Nombres: json["equipo2_nombres"],
            resultado: json["resultado"],
            numSets: json["num_sets"],
            puntuacionEquipo1: json["puntuacion_equipo1"],
            puntuacionEquipo2: json["puntuacion_equipo2"],
        );

        Map<String, dynamic> toJson() => {
            "equipo1_nombres": equipo1Nombres,
            "equipo2_nombres": equipo2Nombres,
            "resultado": resultado,
            "num_sets": numSets,
            "puntuacion_equipo1": puntuacionEquipo1,
            "puntuacion_equipo2": puntuacionEquipo2,
        };
    }



<!-- Llamar a cartar las 20 -->


<!-- Enviar Cantar las 20 -->
{
  "idBaraja": "abc123",
  "idJugador": "jugador1",
  "tipo": "20"
}



<!-- Me devuelve Cantar las 20 Error-->
{
    "success": false,
    "mensaje": "Faltan datos: idBaraja, idJugador o carta"
}
// To parse this JSON data, do
//
//     final cantarLas20ResponseError = cantarLas20ResponseErrorFromJson(jsonString);

import 'dart:convert';

CantarLas20ResponseError cantarLas20ResponseErrorFromJson(String str) => CantarLas20ResponseError.fromJson(json.decode(str));

String cantarLas20ResponseErrorToJson(CantarLas20ResponseError data) => json.encode(data.toJson());

class CantarLas20ResponseError {
    bool success;
    String mensaje;

    CantarLas20ResponseError({
        required this.success,
        required this.mensaje,
    });

    factory CantarLas20ResponseError.fromJson(Map<String, dynamic> json) => CantarLas20ResponseError(
        success: json["success"],
        mensaje: json["mensaje"],
    );

    Map<String, dynamic> toJson() => {
        "success": success,
        "mensaje": mensaje,
    };
}



<!-- Me devuelve Cantar las 20 Exitoso-->
