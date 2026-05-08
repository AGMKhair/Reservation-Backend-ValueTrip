package com.agmkhair.reservation.controller;

import com.agmkhair.reservation.entry.Airline;
import com.agmkhair.reservation.entry.Booking;
import com.agmkhair.reservation.repository.BookingRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.*;

import java.util.List;
import java.util.UUID;

@RestController
@RequestMapping("/api/booking")
public class BookingController {

    @Autowired
    private BookingRepository bookingRepository;


    @GetMapping
    public List<Booking> getAllBooking() {
        return bookingRepository.findAll();
    }


    @PostMapping
    public Booking createBooking(@RequestBody Booking booking) {

        // টিকেটগুলোর সাথে বুকিং কানেক্ট করা
        if(booking.getTickets() != null) {
            booking.getTickets().forEach(ticket -> ticket.setBooking(booking));
        }

        return bookingRepository.save(booking);
    }
}
