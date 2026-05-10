package com.agmkhair.reservation.controller;

import com.agmkhair.reservation.dto.AirlineResponse;
import com.agmkhair.reservation.entry.Airline;
import com.agmkhair.reservation.entry.Booking;
import com.agmkhair.reservation.entry.Flight;
import com.agmkhair.reservation.repository.AirlineRepository;
import com.agmkhair.reservation.repository.BookingRepository;
import com.agmkhair.reservation.service.FileStorageService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.MediaType;
import org.springframework.http.ResponseEntity;
import org.springframework.stereotype.Service;
import org.springframework.util.StringUtils;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.multipart.MultipartFile;

import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.nio.file.StandardCopyOption;
import java.text.Normalizer;
import java.util.*;

@RestController
@RequestMapping("/api/airlines")
@CrossOrigin("*")
public class AirlineController {
    @Autowired
    private AirlineRepository airlineRepository;

    @Autowired
    private FileStorageService fileStorageService;



    @GetMapping
    public List<AirlineResponse> getAllAirlines() {

        List<Airline> airlines = airlineRepository.findAll();

        return airlines.stream().map(a -> {

            AirlineResponse dto = new AirlineResponse();

            dto.setId(a.getId());
            dto.setName(a.getName());
            dto.setLogoUrl(a.getLogoUrl());
            dto.setIconUrl(a.getIconUrl());
            dto.setFlights(a.getFlights());

            return dto;

        }).toList();
    }


    @DeleteMapping("/{id}")
    public ResponseEntity<?> deleteAirline(@PathVariable Long id) {

        if (!airlineRepository.existsById(id)) {
            return ResponseEntity
                    .status(404)
                    .body("Airline not found with id: " + id);
        }

        airlineRepository.deleteById(id);

        return ResponseEntity.ok("Airline deleted successfully");
    }


    @PutMapping(value = "/{id}", consumes = MediaType.MULTIPART_FORM_DATA_VALUE)
    public ResponseEntity<Airline> updateAirline(
            @PathVariable Long id,
            @RequestParam("name") String name,
            @RequestParam(value = "logo", required = false) MultipartFile logo,
            @RequestParam(value = "icon", required = false) MultipartFile icon) {

        // ১. ডাটাবেস থেকে বর্তমান এয়ারলাইন খুঁজে বের করা
        Airline airline = airlineRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Airline not found"));

        // ২. নাম আপডেট করা
        airline.setName(name);

        // ৩. নতুন লোগো আসলে সেটি সেভ করা
        if (logo != null && !logo.isEmpty()) {
            String logoUrl = fileStorageService.storeFile(logo);
            airline.setLogoUrl(logoUrl);
        }

        // ৪. নতুন আইকন আসলে সেটি সেভ করা
        if (icon != null && !icon.isEmpty()) {
            String iconUrl = fileStorageService.storeFile(icon);
            airline.setIconUrl(iconUrl);
        }

        // ৫. আপডেট করা ডাটা সেভ করা
        return ResponseEntity.ok(airlineRepository.save(airline));
    }

    @PostMapping(value = "/upload", consumes = MediaType.MULTIPART_FORM_DATA_VALUE)
    public ResponseEntity<Map<String, String>> upload(
            @RequestParam("file") MultipartFile file,
            @RequestParam("type") String type
    ) {

        System.out.println("Type: " + type);
        System.out.println("File: " + file.getOriginalFilename());

        String url = "https://your-domain.com/" + file.getOriginalFilename();

        Map<String, String> response = new HashMap<>();
        response.put("url", url);

        return ResponseEntity.ok(response);
    }


        private final String BASE_UPLOAD_DIR = "uploads/airlines/";

        @PostMapping(consumes = MediaType.MULTIPART_FORM_DATA_VALUE)
        public ResponseEntity<?> createAirline(
                @RequestParam("name") String name,
                @RequestParam(value = "logo", required = false) MultipartFile logo,
                @RequestParam(value = "icon", required = false) MultipartFile icon
        ) {

            try {

                // airline name sanitize
                String folderName = sanitizeFolderName(name);

                // create airline folder
                Path airlineFolderPath = Paths.get(BASE_UPLOAD_DIR, folderName);

                if (!Files.exists(airlineFolderPath)) {
                    Files.createDirectories(airlineFolderPath);
                }

                String logoUrl = null;
                String iconUrl = null;

                // Save logo
                if (logo != null && !logo.isEmpty()) {

                    String logoFileName =
                            "logo_" + UUID.randomUUID() + "_" +
                                    StringUtils.cleanPath(logo.getOriginalFilename());

                    Path logoPath = airlineFolderPath.resolve(logoFileName);

                    Files.copy(
                            logo.getInputStream(),
                            logoPath,
                            StandardCopyOption.REPLACE_EXISTING
                    );

                    logoUrl =
                            "http://192.168.0.101:8888/uploads/airlines/"
                                    + folderName + "/" + logoFileName;
                }

                // Save icon
                if (icon != null && !icon.isEmpty()) {

                    String iconFileName =
                            "icon_" + UUID.randomUUID() + "_" +
                                    StringUtils.cleanPath(icon.getOriginalFilename());

                    Path iconPath = airlineFolderPath.resolve(iconFileName);

                    Files.copy(
                            icon.getInputStream(),
                            iconPath,
                            StandardCopyOption.REPLACE_EXISTING
                    );

                    iconUrl =
                            "http://192.168.0.101:8888/uploads/airlines/"
                                    + folderName + "/" + iconFileName;
                }

                // Save database
                Airline airline = new Airline();

                airline.setName(name);
                airline.setLogoUrl(logoUrl);
                airline.setIconUrl(iconUrl);

                Airline savedAirline = airlineRepository.save(airline);

                return ResponseEntity.ok(savedAirline);

            } catch (IOException e) {

                return ResponseEntity
                        .internalServerError()
                        .body("File upload failed: " + e.getMessage());
            }
        }

        // folder safe name
        private String sanitizeFolderName(String input) {

            String normalized =
                    Normalizer.normalize(input, Normalizer.Form.NFD);

            return normalized
                    .replaceAll("[^a-zA-Z0-9]", "_")
                    .toLowerCase();
        }
    }

