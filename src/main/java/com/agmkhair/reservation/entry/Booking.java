package com.agmkhair.reservation.entry;
import jakarta.persistence.*;
import lombok.Data;

import java.time.LocalDateTime;
import java.util.List;

@Entity
@Table(name = "bookings")
@Data
public class Booking {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    private String passengerName;

    private String passengerType;

    private String itineraryReference;

    private LocalDateTime createdAt;

    @ManyToOne
    @JoinColumn(name = "flight_id")
    private Flight flight;

    @OneToMany(mappedBy = "booking", cascade = CascadeType.ALL)
    private List<Ticket> tickets;

    // getters & setters
}