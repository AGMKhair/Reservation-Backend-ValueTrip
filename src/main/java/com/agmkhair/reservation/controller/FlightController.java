package com.agmkhair.reservation.controller;

import com.agmkhair.reservation.entry.Flight;
import com.agmkhair.reservation.repository.FlightRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.util.List;
import java.util.UUID;

@RestController
@RequestMapping("/api/flights")
@CrossOrigin("*")
public class FlightController {
    @Autowired
    private FlightRepository flightRepository;


    @GetMapping
    public ResponseEntity<List<Flight>> getAllFlight() {

        List<Flight> flights = flightRepository.findAll();

        return ResponseEntity.ok(flights);
    }

    @PostMapping
    public Flight createFlight(@RequestBody Flight flight) {

        return flightRepository.save(flight);
    }

    @GetMapping("/{id}")
    public ResponseEntity<Flight> getFlight(@PathVariable String id) {
        return flightRepository.findById(Long.valueOf(id))
                .map(ResponseEntity::ok)
                .orElse(ResponseEntity.notFound().build());
    }

    @DeleteMapping("/{id}")
    public ResponseEntity<Void> deleteFlight(@PathVariable String id) {

        return flightRepository.findById(Long.valueOf(id))
                .map(flight -> {
                    flightRepository.delete(flight);
                    return ResponseEntity.noContent().<Void>build();
                })
                .orElse(ResponseEntity.notFound().build());
    }

    @PutMapping("/{id}")
    public ResponseEntity<Flight> updateFlight(
            @PathVariable String id,
            @RequestBody Flight updatedFlight
    ) {

        return flightRepository.findById(Long.valueOf(id))
                .map(existingFlight -> {

                    // basic fields update
                    existingFlight.setFlightName(updatedFlight.getFlightName());
                    existingFlight.setFlightType(updatedFlight.getFlightType());
                    existingFlight.setAirline(updatedFlight.getAirline());
                    existingFlight.setMeal01(updatedFlight.getMeal01());
                    existingFlight.setMeal02(updatedFlight.getMeal02());

                    Flight saved = flightRepository.save(existingFlight);
                    return ResponseEntity.ok(saved);

                })
                .orElse(ResponseEntity.notFound().build());
    }


}