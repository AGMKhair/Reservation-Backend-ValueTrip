package com.agmkhair.reservation.entry;

import jakarta.persistence.*;
import lombok.Data;
import org.hibernate.annotations.CreationTimestamp;


import java.time.LocalDateTime;
import java.util.List;


@Entity
@Data
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
    private String checkInBaggage;
    private String cabinBaggage;
    private String meal01;
    private String meal02;

    @OneToMany(mappedBy = "flight", cascade = CascadeType.ALL)
    private List<FlightLeg> legs;

    @CreationTimestamp
    private LocalDateTime createdAt;
}