package com.agmkhair.reservation.entry;

import com.fasterxml.jackson.annotation.JsonIgnore;
import com.fasterxml.jackson.annotation.JsonManagedReference;
import jakarta.persistence.*;
import lombok.Getter;
import lombok.Setter;
import org.hibernate.annotations.CreationTimestamp;

import java.time.LocalDateTime;
import java.util.List;

@Entity
@Getter
@Setter
@Table(name = "flights")
public class Flight {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne
    @JoinColumn(name = "airline_id")
    private Airline airline;

    private String flightName;
    private String flightType;
    private String flightNo;
    private String departureTimeFirst;
    private String arrivalTimeFirst;
    private String departureTimeSecond;
    private String arrivalTimeSecond;
    private String fromFirstAirport;
    private String toFirstAirport;
    private String fromSecondAirport;
    private String toSecondAirport;
    private String checkInBaggage;
    private String cabinBaggage;
    private String meal01;
    private String meal02;


    @CreationTimestamp
    private LocalDateTime createdAt;
}