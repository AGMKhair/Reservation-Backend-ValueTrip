package com.agmkhair.reservation.dto;

import com.agmkhair.reservation.entry.Airline;
import com.agmkhair.reservation.entry.Flight;
import lombok.Data;

import java.util.List;

@Data
public class AirlineResponse {
    private Long id;

    private String name;
    private String logoUrl;
    private String iconUrl;

    private List<Flight> flights;


}
