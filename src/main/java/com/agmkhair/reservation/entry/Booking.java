package com.agmkhair.reservation.entry;

import com.fasterxml.jackson.annotation.JsonProperty;
import jakarta.persistence.*;
import lombok.Getter;
import lombok.Setter;

import java.time.LocalDateTime;
import java.util.List;

@Entity
@Table(name = "bookings")
@Getter
@Setter
public class Booking {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @JsonProperty("passenger_name")
    private String passengerName;

    @JsonProperty("passenger_type")
    private String passengerType;

    @JsonProperty("itinerary_reference")
    private String itineraryReference;

    @JsonProperty("flight_id")
    private Long flightId;

    @JsonProperty("departure_01")
    private String departure01;

    @JsonProperty("landing_01")
    private String landing01;

    @JsonProperty("departure_02")
    private String departure02;

    @JsonProperty("landing_02")
    private String landing02;

    @JsonProperty("booking_type")
    private String bookingType;

    @JsonProperty("flight_type")
    private String flightType;

    @JsonProperty("is_synced")
    private Boolean isSynced;


    private LocalDateTime createdAt;

    @OneToMany(
            mappedBy = "booking",
            cascade = CascadeType.ALL,
            orphanRemoval = true
    )
    private List<Ticket> tickets;
}