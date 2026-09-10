<?php

namespace Database\Seeders;

use App\Models\EquipmentCategory;
use App\Models\EquipmentSubcategory;
use App\Models\EquipmentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EquipmentHierarchySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {

            $structure = [
                'Artificial lift equipment' => [
                    'ESP systems' => [
                        'Submersible pump assembly',
                        'Electric motor',
                        'Protector',
                        'Intake module',
                    ],
                    'Gas lift systems' => [
                        'Gas lift mandrel',
                        'Gas lift injection valve',
                    ],
                    'Rod Lift systems' => [
                        'Beam pump (pump jack)',
                        'Sucker rod',
                        'Rod pump',
                    ],
                    'PCP systems' => [
                        'Progressive cavity pump',
                        'Drive head',
                        'Controller'
                    ],
                    'Plunger Lift' => [
                        'Plunger',
                        'Lubricator',
                        'Bumper spring'
                    ],
                ],

                'Completion equipment' => [
                    'Production packers' => [
                        'Permanent packer',
                        'Retrievable packer',
                    ],
                    'Bridge plugs' => [
                        'Composite plug',
                        'Cast iron plug',
                    ],
                    'Sand Control Screens' => [
                        'Wire-wrapped screen',
                        'Expandable screen',
                        'Premium mesh screen'
                    ],
                    'Multistage Frac Systems' => [
                        'Sliding sleeve system',
                        'Plug-and-perf system',
                        'Ball-drop system'
                    ],
                    'Subsurface Safety Valves' => [
                        'Tubing-retrievable SSSV',
                        'Wireline-retrievable SSSV',
                    ],
                    'Smart Completions' => [
                        'Interval control valve',
                        'Downhole flow control module',
                    ],
                ],

                'Compression Equipment' => [
                    'Concrete Mixers' => [
                        'Truck-mounted concrete mixer',
                        'Portable concrete mixer',
                    ],
                    'Concrete Pumps' => [
                        'Boom pump truck',
                        'Trailer-mounted line pump',
                    ],
                ],

                'Concrete Equipment' => [
                    'Concrete Mixers' => [
                        'Truck-mounted concrete mixer',
                        'Portable concrete mixer',
                    ],
                    'Concrete Pumps' => [
                        'Boom pump truck',
                        'Trailer-mounted line pump',
                    ],
                ],

                'Drilling equipment' => [
                    'Land Drilling Rigs' => [
                        'Box-on-box rig',
                        'Desert rig',
                        'Fast-move skid rig',
                        'Trailer-mounted rig',
                    ],
                    'Jack-up Rigs' => [
                        'Cantilever jackup',
                        'Slot jackup',
                        'Harsh-environment jackup',
                    ],
                    'Semi-submersible Rigs' => [
                        'Moored semi-submersible',
                        'Dynamically positioned semi-submersible',
                    ],
                    'Drillships' => [
                        'Dynamically positioned drillship',
                        'Ultra-deepwater drillship',
                    ],
                    'Workover Rigs' => [
                        'Truck-mounted workover rig',
                        'Mast workover rig',
                        'Snubbing unit'
                    ],
                    'Rig Masts' => [
                        'Mast',
                        'Derrick',
                        'Monkey board'
                    ],
                    'Rig Substructures' => [
                        'Substructure',
                    ],
                    'Hoisting Systems' => [
                        'Drawworks',
                        'Crown block',
                        'Traveling block',
                        'Hook block'
                    ],
                    'Rotary Systems' => [
                        'Rotary table',
                        'Top drive',
                        'Kelly drive',
                        'Swivel'
                    ],
                    'Mud Circulation Systems' => [
                        'Standpipe',
                        'Mud manifold',
                        'Mud hose',
                        'Swivel joint',
                    ],
                    'Rig Power Systems' => [
                        'SCR house',
                        'VFD house',
                        'Rig generator',
                        'Engine skid'
                    ],
                    'Cementing Heads' => [
                        'Cementing head',
                        'Plug container',
                        'Cement manifold'
                    ],
                    'Cementing Pumps' => [
                        'Skid cement unit',
                        'Truck-mounted cementer',
                        'Batch mixer',
                    ],
                    'Casing Running Tools' => [
                        'Casing running tool (CRT)',
                        'Power slips',
                        'Casing tongs',
                        'Pick-up machine',
                    ],
                    'Well Control Equipment' => [
                        'BOP stack',
                        'Annular preventer',
                        'Ram preventer',
                        'Choke manifold',
                    ],
                    'Shale Shakers' => [
                        'Linear motion shaker',
                        'Elliptical shaker',
                        'Shaker screen',
                    ],
                    'Desanders' => [
                        'Cyclone desander',
                    ],
                    'Desilters' => [
                        'Mud cleaner',
                    ],
                    'Degassers' => [
                        'Vacuum degasser',
                        'Centrifugal degasser',
                    ],
                    'Centrifuges' => [
                        'High-speed centrifuge',
                        'Barite recovery centrifuge',
                    ],
                    'Mud Mixing Systems' => [
                        'Mixing hopper',
                        'Jet mixer',
                        'Mud gun',
                    ],
                    'Mud Tanks' => [
                        'Suction pit',
                        'Reserve pit',
                        'Compartment tank',
                    ],
                    'Bulk Handling' => [
                        'Bulk silo',
                        'Pneumatic blower',
                        'Manifold skid',
                    ],
                    'Drill Bits' => [
                        'PDC bit',
                        'Tricone bit',
                        'Coring bit',
                        'Bi-center bit'
                    ],
                    'Mud Motors' => [
                        'Positive displacement motor',
                        'High-torque mud motor'
                    ],
                    'Rotary Steerable Tools' => [
                        'Push-the-bit RSS (rotary steerable system)',
                        'Point-the-bit RSS',
                    ],
                    'MWD Tools' => [
                        'MWD (measurement-while-drilling) pulser',
                        'Gamma logging tool',
                        'Resistivity tool',
                        'Sonic tool',
                    ],
                    'LWD Tools' => [
                        'LWD (logging-while-drilling) sensor suite',
                    ],
                    'Stabilizers' => [
                        'Near-bit stabilizer'
                    ],
                    'Reamers' => [
                        'Underreamer',
                        'Hole opener',
                    ],
                    'Fishing Tools' => [
                        'Overshot',
                        'Spear',
                        'Junk basket',
                        'Magnet'
                    ],
                    'Whipstocks' => [
                        'Whipstock',
                    ],
                    'Mills' => [
                        'Casing mill',
                        'Window mill',
                    ],
                ],

                'Earthmoving equipment' => [
                    'Excavators' => [
                        'Crawler excavator',
                        'Wheeled excavator',
                        'Mini excavator',
                    ],
                    'Bulldozers' => [
                        'Crawler bulldozer',
                        'Wheeled bulldozer',
                    ],
                    'Loaders' => [
                        'Front-end loader',
                        'Skid-steer loader',
                    ],
                    'Graders' => [
                        'Motor grader',
                    ],
                ],

                'Electrical equipment' => [
                    'Gas Turbines' => [
                        'Aeroderivative gas turbine',
                        'Heavy-duty gas turbine',
                    ],
                    'Generators' => [
                        'Diesel generator',
                        'Gas generator',
                    ],
                    'Transformers' => [
                        'Power transformer',
                        'Distribution transformer'
                    ],
                    'Switchgear' => [
                        'Medium-voltage switchgear',
                        'Low-voltage switchgear',
                    ],
                    'Switches' => [
                        'Isolator switch',
                        'Circuit breaker',
                    ],
                    'Motor Control Centers' => [
                        'MCC panel',
                    ],
                    'Drives' => [
                        'Variable speed drive (VSD)',
                        'Soft starter',
                    ],
                    'Distribution Boards' => [
                        'Main distribution board'
                    ],
                    'Electric Motors' => [
                        'Induction motor',
                        'Synchronous motor',
                    ],
                    'UPS Systems' => [
                        'Online UPS',
                        'Battery bank',
                    ],
                    'Electrical Substations' => [
                        'Prefabricated substation',
                    ],
                    'Cable Trays' => [
                        'Ladder-type cable tray',
                        'Perforated cable tray',
                    ],
                ],

                'Flow Control equipment' => [
                    'Choke Manifolds' => [
                        'Adjustable choke manifold',
                        'Positive choke manifold',
                    ],
                    'Test Manifolds' => [
                        'Fracturing test manifold',
                        'Production test manifold',
                    ],
                    'Metering Skids' => [
                        'Single-phase metering skid',
                        'Multiphase metering skid',
                    ],
                ],

                'Instrumentation equipment' => [
                    'Gauges' => [
                        'Pressure gauge',
                        'Temperature gauge',
                        'Level gauge'
                    ],
                    'Transmitters' => [
                        'Pressure transmitter',
                        'Temperature transmitter',
                        'Flow transmitter',
                        'Level transmitter'
                    ],
                    'Control Valves' => [
                        'Globe control valve',
                        'Ball control valve',
                        'Rotary control valve'
                    ],
                    'PLC Systems' => [
                        'Programmable logic controller (PLC)',
                    ],
                    'DCS Systems' => [
                        'Distributed control system (DCS) controller',
                    ],
                    'SIS Systems' => [
                        'Safety instrumented system (SIS) controller',
                    ],
                    'ESD Systems' => [
                        'Emergency shutdown (ESD) system controller',
                    ],
                    'Gas Detectors' => [
                        'Fixed gas detector',
                        'Portable gas detector',
                    ],
                    'Fire Detectors' => [
                        'Flame detector',
                        'Smoke detector',
                    ],
                    'Flow Regulators' => [
                        'Flow control valve',
                    ],
                    'Flow Indicators' => [
                        'Flow indicator',
                    ],
                    'Sensors' => [
                        'Temperature sensor',
                        'Level probe',
                    ],
                ],

                'Lifting equipment' => [
                    'Cranes' => [
                        'Truck crane',
                        'All-terrain crane',
                        'Crawler crane',
                        'Tower crane',
                        'Overhead crane',
                    ],
                    'Forklifts' => [
                        'Diesel forklift',
                        'Rough-terrain forklift',
                    ],
                    'Aerial Lifts' => [
                        'Scissor lift',
                        'Boom lift',
                    ],
                    'Hoists' => [
                        'Chain hoist',
                        'Lever hoist',
                    ],
                ],

                'Perforating equipment' => [
                    'Perforating Systems' => [
                        'Hollow carrier gun',
                        'Tubing-conveyed perforator (TCP)',
                        'Expendable gun',
                    ],
                ],

                'Pipeline equipment' => [
                    'Pipelines' => [
                        'Onshore pipeline',
                        'Offshore pipeline',
                    ],
                    'Pig Launchers/Receivers' => [
                        'Launcher barrel',
                        'Receiver barrel',
                    ],
                ],

                'Pumping equipment' => [
                    'Centrifugal Pumps' => [
                        'Single-stage centrifugal pump',
                        'Multistage centrifugal pump',
                        'API-standard pump',
                    ],
                    'Rotary Pumps' => [
                        'Gear pump',
                        'Screw pump',
                    ],
                    'Reciprocating Pumps' => [
                        'Piston pump',
                        'Plunger pump',
                        'Diaphragm pump'
                    ],
                    'Blowers' => [
                        'Centrifugal blower',
                    ],
                    'Fans' => [
                        'Axial fan',
                    ],
                ],

                'Reactor Equipment' => [
                    'Catalytic Reactors' => [
                        'Hydrogenation reactor',
                        'Catalytic reforming reactor',
                        'Disproportionation reactor',
                        'Isomerization reactor'
                    ],
                    'Polymerization Reactors' => [
                        'Batch polymerization reactor',
                        'Continuous polymerization reactor',
                    ],
                    'Cracking Reactors' => [
                        'Fluid catalytic cracking reactor',
                        'Delayed coker drum',
                    ],
                ],

                'Seperation Equipment' => [
                    'Separators' => [
                        'Horizontal separator',
                        'Vertical separator',
                        'Compact separator',
                    ],
                    'Slug Catchers' => [
                        'Finger-type slug catcher',
                        'Vessel-type slug catcher',
                    ],
                    'Dehydration Units' => [
                        'TEG (glycol) contactor',
                        'Molecular sieve dryer',
                    ],
                    'Sweetening Units' => [
                        'Amine contactor',
                        'Sulfur recovery unit',
                    ],
                    'Produced Water Treatment' => [
                        'Hydrocyclone',
                        'Flotation unit',
                        'Walnut shell filter'
                    ],
                    'Flare Systems' => [
                        'Flare stack',
                        'Knock-out drum',
                        'Ignition system'
                    ],
                    'Compressor Stations' => [
                        'Sieve tray column',
                        'Valve tray column',
                        'Bubble cap column'
                    ],
                    'Packed Columns' => [
                        'Random packing column',
                        'Structured packing column',
                    ],
                ],

                'Storage Equipment' => [
                    'Metal Tanks' => [
                        'Floating Roof',
                        'Fixed Roof',
                    ],
                    'Spherical Tanks' => [
                        'Spherical LPG tank',
                    ],
                    'Non-metal Tanks' => [
                        'FRP (fiberglass) tank',
                    ],
                    'Gas Cabinets' => [
                        'Gas cylinder storage cabinet',
                    ],
                    'Mixing Tanks' => [
                        'Agitated storage tank',
                    ],
                ],

                'Subsea Equipment' => [
                    'Subsea Trees' => [
                        'Horizontal subsea tree',
                        'Vertical subsea tree',
                    ],
                    'Subsea Manifolds' => [
                        'Production manifold',
                        'Injection manifold'
                    ],
                    'Umbilicals' => [
                        'Static umbilical',
                        'Dynamic umbilical'
                    ],
                    'Subsea Pumps' => [
                        'Subsea booster pump',
                        'Subsea compressor unit'
                    ],
                    'Risers' => [
                        'Flexible riser',
                        'Steel catenary riser'
                    ],
                ],

                'Thermal Equipment' => [
                    'Shell & Tube Exchangers' => [
                        'Fixed tubesheet exchanger',
                        'Floating head exchanger',
                        'U-tube exchanger'
                    ],
                    'Plate Exchangers' => [
                        'Gasketed plate exchanger',
                        'Welded plate exchanger'
                    ],
                    'Air Coolers' => [
                        'Fin-fan cooler',
                        'Forced draft cooler'
                    ],
                    'Fired Heaters' => [
                        'Box-type furnace',
                        'Cylindrical furnace',
                        'Tube-type furnace'
                    ],
                    'Boilers' => [
                        'Package boiler',
                        'Heat recovery steam generator (HRSG)'
                    ],
                    'Cooling Towers' => [
                        'Induced draft cooling tower',
                        'Forced draft cooling tower'
                    ],
                ],

                'Valve Equipment' => [
                    'Ball Valves' => [
                        'Floating ball valve',
                        'Trunnion-mounted ball valve',
                        'Rising Stem Ball Valve'
                    ],
                    'Butterfly Valves' => [
                        'Wafer butterfly valve',
                        'Lug butterfly valve',
                        'Rubberlinned Butterfly',
                        'Double offset Butterfly',
                        'Triple Offset Butterfly Valve'
                    ],
                    'Gate Valves' => [
                        'Rising stem gate valve',
                        'Non-rising stem gate valve'
                    ],
                    'Globe Valves' => [
                        'Angle globe valve',
                    ],
                    'Check Valves' => [
                        'Swing check valve',
                        'Lift check valve'
                    ],
                    'Pressure Relief Valves' => [
                        'Spring-loaded PSV (safety valve)',
                        'Pilot-operated PSV'
                    ],
                ],

                'Wellhead Equipment' => [
                    'Surface Wellheads' => [
                        'Casing head',
                        'Tubing head',
                        'Multi-bowl wellhead'
                    ],
                    'Christmas Trees' => [
                        'Conventional tree',
                        'Dual completion tree',
                    ],
                    'Welding Machines' => [
                        'Diesel welding generator',
                        'Electric inverter welder'
                    ],
                    'Wireline Units' => [
                        'Truck-mounted wireline unit',
                        'Skid-mounted wireline unit',
                        'Offshore wireline unit'
                    ],
                    'Pressure Control' => [
                        'Wireline grease injector',
                        'Wireline lubricator',
                        'Wireline BOP'
                    ],
                    'Open-hole Logging' => [
                        'Resistivity logging tool',
                        'Density logging tool',
                        'Sonic logging tool'
                    ],
                    'Cased-hole Logging' => [
                        'Cement bond logging tool',
                        'Production logging tool'
                    ],
                    'Slickline Tools' => [
                        'Pulling tool',
                        'Running tool',
                        'Gauge cutter',
                        'Jar'
                    ],
                ],

                'Truck equipment' => [
                    'Light Duty Trucks' => [
                        'Pickup truck',
                        '4x4 pickup',
                        'Crew cab pickup',
                    ],
                    'Flatbed' => [
                        'Flatbed truck',
                        'Stake truck',
                        'Box truck',
                    ],
                    'Heavy Duty Trucks' => [
                        'Tractor head',
                        'Prime mover',
                        'Heavy haul truck'
                    ],
                    'Dump Trucks' => [
                        'Articulated dump truck',
                        'Rigid dump truck',
                        'Tipper truck'
                    ],
                    'Tanker Trucks' => [
                        'Fuel tanker truck',
                        'Water tanker truck',
                        'Chemical tanker truck'
                    ],
                    'Vacuum Trucks' => [
                        'Vacuum tanker',
                        'Hydro-vac truck',
                        'Sludge truck'
                    ],
                    'Cementing Trucks' => [
                        'Bulk cement truck',
                        'Pneumatic cement carrier'
                    ],
                    'Fracturing Trucks' => [
                        'Frac pump truck',
                        'Blender truck',
                        'Hydration unit truck',
                    ],
                    'Logging Trucks' => [
                        'Wireline logging truck',
                        'Slickline unit truck',
                    ],
                    'Crane Trucks' => [
                        'Boom truck',
                        'Knuckle boom truck',
                    ],
                    'Service Trucks' => [
                        'Lube truck',
                        'Maintenance truck',
                        'Field service truck',
                    ],
                    'Fire Trucks' => [
                        'Industrial fire truck',
                        'Foam tender',
                    ],
                    'Utility Trucks' => [
                        'Utility body truck',
                        'Line truck',
                        'Cable reel truck',
                    ],
                    'Refrigerated Trucks' => [
                        'Reefer truck',
                        'Insulated box truck',
                    ],
                    'Lowbed Trucks' => [
                        'Lowboy trailer truck',
                        'Equipment transport truck',
                    ],
                    'Winch Trucks' => [
                        'Oilfield winch truck',
                        'Recovery truck',
                    ],
                    'Personnel Trucks' => [
                        'Crew transport bus',
                        'Personnel carrier'
                    ],
                    'Pipe Transport Trucks' => [
                        'Pipe rack truck',
                        'Tubular hauling truck',
                    ],
                    'Water Spray Trucks' => [
                        'Dust suppression truck',
                        'Road spray truck',
                    ],
                    'Garbage Trucks' => [
                        'Compactor truck',
                        'Roll-off truck',
                    ],
                ],

                'Midstream Equipment' => [
                    'Metering Stations' => [
                        'Fiscal flow meter',
                        'LACT unit (lease automatic custody transfer)',
                    ],
                    'Compressor Stations' => [
                        'Gas compressor train',
                        'Booster station'
                    ],
                    'Loading Systems' => [
                        'Marine loading arm',
                        'Truck loading arm'
                    ],
                ],
            ];

            foreach ($structure as $categoryName => $subcategories) {

                $category = EquipmentCategory::firstOrCreate(
                    ['name' => $categoryName],
                    ['is_global' => true]
                );

                foreach ($subcategories as $subCategoryName => $types) {

                    $subCategory = EquipmentSubcategory::firstOrCreate(
                        [
                            'name' => $subCategoryName,
                            'equipment_category_id' => $category->id,
                        ]
                    );

                    foreach ($types as $typeName) {

                        EquipmentType::firstOrCreate(
                            [
                                'name' => $typeName,
                                'equipment_subcategory_id' => $subCategory->id,
                            ]
                        );
                    }
                }
            }
        });
    }
}
