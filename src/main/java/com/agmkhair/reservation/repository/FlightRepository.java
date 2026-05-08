package com.agmkhair.reservation.repository;

import com.agmkhair.reservation.entry.Flight;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

@Repository
public interface FlightRepository extends JpaRepository<Flight, String> {}
