package com.agmkhair.reservation.controller;

import com.agmkhair.reservation.dto.BookingRequestDTO;
import com.agmkhair.reservation.entry.Airline;
import com.agmkhair.reservation.entry.Booking;
import com.agmkhair.reservation.entry.Flight;
import com.agmkhair.reservation.repository.BookingRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.*;

import java.time.LocalDateTime;
import java.util.List;
import java.util.UUID;

@RestController
@RequestMapping("/api/bookings")
@CrossOrigin("*")
public class BookingController {

    @Autowired
    private BookingRepository bookingRepository;


    @GetMapping
    public List<Booking> getAllBooking() {
        return bookingRepository.findAll();
    }


    @PostMapping
    public Booking createBooking(@RequestBody BookingRequestDTO dto) {

        Booking booking = new Booking();

        // --------------------
        // STRING FIELDS (NULL SAFE)
        // --------------------
        if (dto.getPassengerName() != null) {
            booking.setPassengerName(dto.getPassengerName());
        }

        if (dto.getPassengerType() != null) {
            booking.setPassengerType(dto.getPassengerType());
        }

        if (dto.getItineraryReference() != null) {
            booking.setItineraryReference(dto.getItineraryReference());
        }

        if (dto.getBookingType() != null) {
            booking.setBookingType(dto.getBookingType());
        }

        if (dto.getFlightType() != null) {
            booking.setFlightType(dto.getBookingType());
        }

        if (dto.getIsSynced() != null) {
            booking.setIsSynced(dto.getIsSynced());
        }

        // --------------------
        // EXTRA FIELDS
        // --------------------
        if (dto.getDeparture01() != null) {
            booking.setDeparture01(dto.getDeparture01());
        }

        if (dto.getLanding01() != null) {
            booking.setLanding01(dto.getLanding01());
        }
        if (dto.getDeparture02() != null) {
            booking.setDeparture02(dto.getDeparture02());
        }

        if (dto.getLanding02() != null) {
            booking.setLanding02(dto.getLanding02());
        }

        // --------------------
        // RELATION (FLIGHT)
        // --------------------
        if (dto.getFlightId() != null) {
            booking.setFlightId(dto.getFlightId());
        }

        // --------------------
        // SYSTEM FIELDS
        // --------------------
        booking.setCreatedAt(LocalDateTime.now());

        return bookingRepository.save(booking);
    }
}
