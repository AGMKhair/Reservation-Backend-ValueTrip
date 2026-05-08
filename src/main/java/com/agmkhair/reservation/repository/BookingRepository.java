package com.agmkhair.reservation.repository;

import com.agmkhair.reservation.entry.Booking;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

@Repository
public interface BookingRepository extends JpaRepository<Booking, String> {}
