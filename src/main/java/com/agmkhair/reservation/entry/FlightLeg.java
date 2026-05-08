package com.agmkhair.reservation.entry;

import com.fasterxml.jackson.annotation.JsonIgnore;
import jakarta.persistence.*;
import lombok.Data;

@Entity
@Data
@Table(name = "flight_legs")
public class FlightLeg {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne
    @JoinColumn(name = "flight_id")
    @JsonIgnore
    private Flight flight;

    private Integer legIndex;
    private String flightNo;
    private String fromAirport;
    private String toAirport;
    private String departureTime;
    private String arrivalTime;
}