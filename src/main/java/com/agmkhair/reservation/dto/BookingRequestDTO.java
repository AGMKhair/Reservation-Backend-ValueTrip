package com.agmkhair.reservation.dto;

import lombok.Getter;
import lombok.Setter;

import com.fasterxml.jackson.annotation.JsonProperty;

import java.util.List;

@Getter
@Setter
public class BookingRequestDTO {

    private Long id;

    @JsonProperty("flight_id")
    private Long flightId;

    @JsonProperty("passenger_name")
    private String passengerName;

    @JsonProperty("passenger_type")
    private String passengerType;

    private String gender;

    @JsonProperty("itinerary_reference")
    private String itineraryReference;

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

    private List<Object> tickets;

    @JsonProperty("created_at")
    private String createdAt;

    @JsonProperty("is_synced")
    private Boolean isSynced;
}