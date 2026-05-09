package com.agmkhair.reservation.entry;

import com.fasterxml.jackson.annotation.JsonIgnore;
import com.fasterxml.jackson.annotation.JsonManagedReference;
import jakarta.persistence.*;
import lombok.Data;

import java.util.List;

@Entity
@Data
@Table(name = "flight_legs")
public class FlightLeg {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @JoinColumn(name = "flight_id")
    private Long flight;


    private Integer legIndex;
    private String flightNo;
    private String fromAirport;
    private String toAirport;
    private String departureTime;
    private String arrivalTime;

}