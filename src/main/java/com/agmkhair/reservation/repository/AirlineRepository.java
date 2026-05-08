package com.agmkhair.reservation.repository;

import com.agmkhair.reservation.entry.Airline;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

@Repository
public interface AirlineRepository extends JpaRepository<Airline, Long> {
}