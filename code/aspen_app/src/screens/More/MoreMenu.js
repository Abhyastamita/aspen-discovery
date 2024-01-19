import { Entypo, MaterialIcons } from '@expo/vector-icons';
import { ListItem } from '@rneui/themed';
import * as WebBrowser from 'expo-web-browser';
import _ from 'lodash';
import moment from 'moment';
import { Box, Divider, FlatList, HStack, Icon, Pressable, Text, useColorModeValue, useContrastText, useToken, VStack } from 'native-base';
import React from 'react';
import { LanguageContext, LibraryBranchContext, LibrarySystemContext } from '../../context/initialContext';
import { navigate } from '../../helpers/RootNavigator';
import { getTermFromDictionary } from '../../translations/TranslationService';
import { GLOBALS } from '../../util/globals';
import { LIBRARY } from '../../util/loadLibrary';

export const MoreMenu = () => {
     const { menu } = React.useContext(LibrarySystemContext);
     const hasMenuItems = _.size(menu);

     return (
          <Box>
               <VStack space="4" my="2" mx="1">
                    <MyLibrary />
                    <Divider />

                    <VStack divider={<Divider />} space="4">
                         {hasMenuItems > 0 ? <FlatList data={Object.keys(menu)} renderItem={({ item }) => <MenuLink links={menu[item]} />} /> : null}
                         <VStack space="3">
                              <VStack>
                                   <Settings />
                                   <PrivacyPolicy />
                              </VStack>
                         </VStack>
                    </VStack>
               </VStack>
          </Box>
     );
};

const MyLibrary = () => {
     const { library } = React.useContext(LibrarySystemContext);
     const { location } = React.useContext(LibraryBranchContext);
     const { language } = React.useContext(LanguageContext);

     const contrastTextColor = useToken('colors', useContrastText('primary.400'));

     let isClosedToday = false;
     let hoursLabel = '';
     if (location.hours) {
          const day = moment().day();
          if (_.find(location.hours, _.matchesProperty('day', day))) {
               let todaysHours = _.filter(location.hours, { day: day });
               if (todaysHours[0]) {
                    todaysHours = todaysHours[0];
                    if (todaysHours.isClosed) {
                         isClosedToday = true;
                         hoursLabel = getTermFromDictionary(language, 'location_closed');
                    } else {
                         const closingText = todaysHours.close;
                         const time1 = closingText.split(':');
                         const openingText = todaysHours.open;
                         const time2 = openingText.split(':');
                         const closeTime = moment().set({ hour: time1[0], minute: time1[1] });
                         const openTime = moment().set({ hour: time2[0], minute: time2[1] });
                         const nowTime = moment();
                         const stillOpen = moment(nowTime).isBefore(closeTime);
                         const stillClosed = moment(openTime).isBefore(nowTime);
                         if (!stillOpen) {
                              isClosedToday = true;
                              hoursLabel = getTermFromDictionary(language, 'location_closed');
                         }
                         if (!stillClosed) {
                              isClosedToday = true;
                              let openingTime = moment(openTime).format('h:mm A');
                              hoursLabel = 'Closed until ' + openingTime;
                         } else {
                              let closingTime = moment(closeTime).format('h:mm A');
                              hoursLabel = 'Open until ' + closingTime;
                         }
                    }
               }
          }
     }

     return (
          <Box m="4" bg="primary.400" p="6" rounded="xl">
               <Pressable display="flex" flexDirection="row" onPress={() => navigate('MyLibrary')} space="1" alignItems="center" justifyContent="space-between">
                    <VStack>
                         <Text bold fontSize="16" color={contrastTextColor}>
                              {library.displayName}
                         </Text>
                         {library.displayName !== location.displayName ? (
                              <Text bold color={contrastTextColor}>
                                   {location.displayName}
                              </Text>
                         ) : null}
                         {hoursLabel ? <Text color={contrastTextColor}>{hoursLabel}</Text> : null}
                    </VStack>
                    <Icon as={MaterialIcons} name="chevron-right" size="7" color={contrastTextColor} />
               </Pressable>
          </Box>
     );
};

const Settings = () => {
     const { language } = React.useContext(LanguageContext);

     return (
          <Pressable px="2" py="3" onPress={() => navigate('MyPreferences')}>
               <HStack space="1" alignItems="center">
                    <Icon as={MaterialIcons} name="chevron-right" size="7" />
                    <Text fontWeight="500">{getTermFromDictionary(language, 'preferences')}</Text>
               </HStack>
          </Pressable>
     );
};

const PrivacyPolicy = () => {
     const { library } = React.useContext(LibrarySystemContext);
     const { language } = React.useContext(LanguageContext);

     const backgroundColor = useToken('colors', useColorModeValue('warmGray.200', 'coolGray.900'));
     const textColor = useToken('colors', useColorModeValue('gray.800', 'coolGray.200'));
     const browserParams = {
          enableDefaultShareMenuItem: false,
          presentationStyle: 'popover',
          showTitle: false,
          toolbarColor: backgroundColor,
          controlsColor: textColor,
          secondaryToolbarColor: backgroundColor,
     };

     const openURL = async (url) => {
          const newUrl = appendQuery(url, 'minimalInterface=true');
          console.log(newUrl);
          WebBrowser.openBrowserAsync(url + '&minimalInterface=true', browserParams);
     };

     return (
          <Pressable px="2" py="3" onPress={() => openURL(LIBRARY.appSettings.privacyPolicy ?? GLOBALS.privacyPolicy)}>
               <HStack space="1" alignItems="center">
                    <Icon as={MaterialIcons} name="chevron-right" size="7" />
                    <Text fontWeight="500">{getTermFromDictionary(language, 'privacy_policy')}</Text>
               </HStack>
          </Pressable>
     );
};

const MenuLink = (payload) => {
     const { library } = React.useContext(LibrarySystemContext);
     const categories = payload.links;
     let hasMultiple = false;
     if (_.size(categories) > 1) {
          hasMultiple = true;
     }
     let categoryLabel = _.sample(categories);
     categoryLabel = categoryLabel.category;

     const backgroundColor = useToken('colors', useColorModeValue('warmGray.200', 'coolGray.900'));
     const textColor = useToken('colors', useColorModeValue('gray.800', 'coolGray.200'));
     const browserParams = {
          enableDefaultShareMenuItem: false,
          presentationStyle: 'popover',
          showTitle: false,
          toolbarColor: backgroundColor,
          controlsColor: textColor,
          secondaryToolbarColor: backgroundColor,
     };

     const [expanded, setExpanded] = React.useState(false);

     function isValidHttpUrl(str) {
          const pattern = new RegExp(
               '^([a-zA-Z]+:\\/\\/)?' + // protocol
                    '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|' + // domain name
                    '((\\d{1,3}\\.){3}\\d{1,3}))' + // OR IP (v4) address
                    '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*' + // port and path
                    '(\\?[;&a-z\\d%_.~+=-]*)?' + // query string
                    '(\\#[-a-z\\d_]*)?$', // fragment locator
               'i'
          );
          return pattern.test(str);
     }

     const openURL = async (url) => {
          let formattedUrl = url;
          if (!isValidHttpUrl(url)) {
               /* Assume the URL is a relative one to Aspen Discovery */
               formattedUrl = _.trimEnd(library.baseUrl, '/') + '/' + _.trimStart(url, '/');
          }
          if (formattedUrl.includes(library.baseUrl)) {
               /* If Aspen Discovery, append minimalInterface to clean up the UI */
               formattedUrl = appendQuery(formattedUrl, 'minimalInterface=true');
          }
          WebBrowser.openBrowserAsync(formattedUrl, browserParams);
     };

     if (hasMultiple) {
          return (
               <>
                    <ListItem.Accordion
                         containerStyle={{
                              backgroundColor: 'transparent',
                              paddingBottom: 2,
                              paddingLeft: 0,
                              paddingTop: 0,
                         }}
                         content={
                              <>
                                   <HStack space="1" alignItems="center" px="2" py="3">
                                        <Icon as={expanded ? Entypo : MaterialIcons} name={expanded ? 'chevron-small-down' : 'chevron-right'} size="7" />
                                        <VStack w="100%">
                                             <Text fontWeight="500">{categoryLabel}</Text>
                                        </VStack>
                                   </HStack>
                              </>
                         }
                         noIcon={true}
                         isExpanded={expanded}
                         onPress={() => {
                              setExpanded(!expanded);
                         }}>
                         {_.map(categories, function (item, index) {
                              return (
                                   <ListItem
                                        key={index}
                                        containerStyle={{
                                             backgroundColor: 'transparent',
                                             paddingTop: 1,
                                        }}
                                        borderBottom
                                        onPress={() => openURL(item.url)}>
                                        <HStack space="1" alignItems="center" ml={4}>
                                             <Icon as={MaterialIcons} name="chevron-right" size="7" />
                                             <VStack w="100%">
                                                  <Text fontWeight="500">{item.linkText}</Text>
                                             </VStack>
                                        </HStack>
                                   </ListItem>
                              );
                         })}
                    </ListItem.Accordion>
               </>
          );
     }

     return (
          <>
               {_.map(categories, function (item, index) {
                    return (
                         <Pressable key={index} px="2" py="3" rounded="md" onPress={() => openURL(item.url)}>
                              <HStack space="1" alignItems="center">
                                   <Icon as={MaterialIcons} name="chevron-right" size="7" />
                                   <VStack w="100%">
                                        <Text fontWeight="500">{item.linkText}</Text>
                                   </VStack>
                              </HStack>
                         </Pressable>
                    );
               })}
          </>
     );
};

function appendQuery(url, query) {
     let newQuery = _.trim(query, '?&');

     if (newQuery) {
          let glue = url.includes('?') === false ? '?' : '&';
          return url + glue + newQuery;
     }

     return url;
}