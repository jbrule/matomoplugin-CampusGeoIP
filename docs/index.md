## Documentation

This is a Location Provider plugin that supports GeoIP resolution for configured networks. We use it to resolve IPs to locations across our campuses. It theoretically supports IPv6 but that hasn't been heavily tested. It has been used since 2019 to resolve IPs from 23,000+ private network ranges across 95 locations.

Upon install this plugin creates two table in the database 
* ```campusgeoip_locations``` - this table needs to be populated directly (Sequel Ace, phpMyAdmin) as there is no UI built to do that. ![Locations Table](/screenshots/locations.png)
* ```campusgeoip_networks``` - this table is populated and maintained using the following console command  ```./console CampusGeoIP:update-networks http://<yournetworkdata>```


Make sure your campus and CampusCode fields match across the locations table and network data as that is what the join is preformed on.

# Format of Networks data file (yournetworkdata)

```
NetworkFirstAsInteger|NetworkLastAsInteger|NetworkAsInteger|NetworkWithCIDR|NetworkAddress|NetworkCIDR|NetworkMask|NetworkHostCount|NetworkComment|Region|CampusCode|BuildingCode|FloorCode|Use|Disabled
167772160|167772415|167772160|10.0.0.0/24|10.0.0.0|24|255.255.255.0|256|My Network Comment|Region Name|CA|BA|01||false
167816704|167816959|167816704|10.0.174.0/24|10.0.174.0|24|255.255.255.0|256|My Network Comment 2|Region Name|CA|BA|03||false
```

The following fields are the only ones parsed. The plugin was written against this format because it was what our IPAM delivers. Ideally this would be configurable. Pull Requests welcome.
["cidr"=>3,"note"=>8,"region"=>9,"campus"=>10,"building"=>11,"floor"=>12,"use"=>13,"disabled"=>14]

# Enable
When you have all the data populated go into the Matomo admin and switch the location provider under ![Geolocation](/screenshots/GeoIP Screen.png).

# Wish List
Pull Requests are welcome if you would like to implement these features
* Clean implementation that includes building and floor in reports
* Support local network data file
* Configurable network data parser
* GUI for managing locations and networks
