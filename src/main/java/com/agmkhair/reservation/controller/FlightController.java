package com.agmkhair.reservation.controller;

import com.agmkhair.reservation.entry.Flight;
import com.agmkhair.reservation.repository.FlightRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.util.UUID;

@RestController
@RequestMapping("/api/flights")
public class FlightController {
    @Autowired
    private FlightRepository flightRepository;

    @PostMapping
    public Flight createFlight(@RequestBody Flight flight) {

        // Relationship সেট করা (Legs-এর সাথে)
        if (flight.getLegs() != null) {
            flight.getLegs().forEach(leg -> leg.setFlight(flight));
        }

        return flightRepository.save(flight);
    }

    @GetMapping("/{id}")
    public ResponseEntity<Flight> getFlight(@PathVariable String id) {
        return flightRepository.findById(id)
                .map(ResponseEntity::ok)
                .orElse(ResponseEntity.notFound().build());
    }
}